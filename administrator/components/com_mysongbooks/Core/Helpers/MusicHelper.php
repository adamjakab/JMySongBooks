<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die();
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Repository\ChordRepository;
use MySongBooks\Core\Repository\ChordTypeRepository;
use MySongBooks\Core\Entity\Chord;
use MySongBooks\Core\Entity\ChordLayout;

/**
 * Generic music methods
 *
 * Class MusicHelper
 */
class MusicHelper {
	/**
	 * This array is used to translate root_note(1-12) values to human readable note names in different notations
	 * @var array
	 */
	private static $chromatic_scale_notes = [
		"C" => [1=>'C', 2=>'C#', 3=>'D', 4=>'D#', 5=>'E', 6=>'F', 7=>'F#', 8=>'G', 9=>'G#', 10=>'A', 11=>'A#', 12=>'B'],
		"Do" => [1=>'Do', 2=>'Do#', 3=>'Re', 4=>'Re#', 5=>'Mi', 6=>'Fa', 7=>'Fa#', 8=>'Sol', 9=>'Sol#', 10=>'La', 11=>'La#', 12=>'Si'],
	];


	/**
	 *
	 *      ABCDEFGRMSL
	 *      aeio l
	 *
	 *
	 * @param string $chordString
	 * @return array
	 */
	public static function identifyChordFromString($chordString) {
		$answer = [];
		$answer["chordString"] = $chordString;
		$answer["valid"] = false;

		//#1 - Identify note name ("♭"===bemolle)
		$pattern = '/^[ABCDEFGRMSL][aeio]?l?#?/';
		if(preg_match($pattern, $chordString, $m)) {
			$answer["noteName"] = $m[0];
			$answer["chordIdentifier"] = str_replace($answer["noteName"], "", $chordString);
		}

		//#2 - Find note number (1-12)
		if(isset($answer["noteName"])) {
			$found = false;
			foreach(self::$chromatic_scale_notes as $k => $notesArray) {
				foreach($notesArray as $noteNumber => $noteName) {
					if($answer["noteName"] == $noteName) {
						$answer["noteNumber"] = $noteNumber;
						$answer["noteNamingScheme"] = $k;
						$found = true;
						break;
					}
				}
				if($found) {
					break;
				}
			}
		}

		//#3 - Find Chord from database (root_note===$answer["noteNumber"], abbreviation+alt_abbreviations ~= $answer["chordIdentifier"])
		if(isset($answer["noteNumber"])) {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var ChordRepository $repo */
			$repo = $em->getRepository('MySongBooks\Core\Entity\Chord');
			$chord = $repo->getChordByRootNoteAndAbbrString($answer["noteNumber"], $answer["chordIdentifier"]);
			if($chord) {
				$answer["valid"] = true;
				$answer["chordId"] = $chord->getId();
				$answer["chordIdentificator"] = $chord->getType()->getAbbreviation();//abbreviation of type so "" is major, "-" is minor...
				$answer["chordName"] = $chord->getName();
				$answer["chordAltNames"] = $chord->getAlternativeNames();
				$answer["chordLayouts"] = $chord->countChordLayouts();
				if($answer["chordLayouts"]) {
					/** @var ChordLayout $CL */
					$CL = $chord->getChordLayouts()->first();
					$answer["chordLayoutScheme"] = $CL->getScheme();
				}
			}
		}
		return($answer);
	}

	/**
	 * Returns song content marked up with chords data and the array of chords info
	 * @param string $songText - this is how Song::content is saved in edit form (no html)
	 * @return \stdClass
	 */
	public static function getSongMarkup($songText) {
		/**
		 * if we have adjecent chords '{Re}{Re4}{Re}' put them together in '{Re,Re4,Re}'
		 * this is so that we can render them together inside the <chords> tag
		 */
		$text = str_replace("}{",",",$songText);

		//match all note markdown notations
		$pattern = '/\{[^}]*\}/';
		preg_match_all($pattern, $text, $m);
		if(isset($m[0])&&count($m[0])) {
			$matches = array_unique($m[0]);
			$chords = [];
			$uniqueChords = [];
			/**
			 * PASS#1 - build the chords array
			 */
			foreach($matches as $match) {
				$txt = str_replace("{", "", str_replace("}", "", $match));
				$chordObj = new \stdClass();
				$chordObj->chords = explode(",", $txt);
				$chordObj->foundBy = $match;
				$chordObj->pregReplacePattern = '/' . preg_quote($match) . '/';
				$chordObj->pregReplaceReplacement = $chordObj->foundBy;//do it later when we have more info about chords (pass#3)
				array_push($chords, $chordObj);
				//add these chords to unique chords array
				$uniqueChords = array_merge($uniqueChords, $chordObj->chords);
			}
			//echo '<pre>CHORDS(#1): ' .  htmlentities(print_r($chords, true)) . '</pre>';

			/**
			 * PASS#2 - get ids for each chord
			 */
			$uniqueChords = array_values(array_unique($uniqueChords));
			$identifiedChords = [];
			foreach($uniqueChords as $chordString) {
				$identifiedChords[$chordString] = self::identifyChordFromString($chordString);
			}
			//echo '<pre>IDENTIFIED CHORDS(#2): ' .  htmlentities(print_r($identifiedChords, true)) . '</pre>';

			/**
			 * PASS#3 - create substitutions
			 */
			foreach($chords as &$chordObj) {
				$validChords = 0;
				$chordReplacements = [];
				foreach($chordObj->chords as $chord) {
					if(isset($identifiedChords[$chord]) && $identifiedChords[$chord]["valid"]) {
						$IC = $identifiedChords[$chord];
						$chordReplacements[] = '<chord data-name="'.$chord.'" data-id="'.$IC["chordId"].'">'.$chord.'</chord>';
						$validChords++;
					}
				}
				if($validChords === count($chordObj->chords)) {
					$chordObj->pregReplaceReplacement = '<chords data-count="'.count($chordObj->chords).'">' . implode("",$chordReplacements)  . '</chords>';
				}
			}
			//echo '<pre>CHORDS(#3): ' .  htmlentities(print_r($chords, true)) . '</pre>';


			/**
			 * PASS#4 - $identifiedChords can be still containing duplicate chords because the text might have used
			 * both "C" and "Do" notations or other tricks like C#===D♭[not yet checked for]
			 * so we need to recreate the array by checking uniqueness by chordID
			 * this will also eliminate not "valid" chords
			 */
			$tmp = $identifiedChords;//this means copy for arrays
			$identifiedChords = [];
			foreach($tmp as $c) {
				if($c["valid"] && isset($c["chordId"]) && !array_key_exists($c["chordId"], $identifiedChords)) {
					$identifiedChords[$c["chordId"]] = $c;
				}
			}

			/**
			 * PASS#5 - build the pattern and substitution arrays for final preg_replace
			 */
			$pA = [];
			$sA = [];
			foreach($chords as &$chordObj) {
				array_push($pA, $chordObj->pregReplacePattern);
				array_push($sA, $chordObj->pregReplaceReplacement);
			}
			//echo '<pre>PATTERNS: ' .  htmlentities(print_r($pA, true)) . '</pre>';
			//echo '<pre>SUBSTITUTIONS: ' .  htmlentities(print_r($sA, true)) . '</pre>';

			/**
			 * PASS#6 - do the replacements
			 */
			$text = preg_replace($pA, $sA, $text);
		}

		/**
		 * PASS#7 - wrap each line in <line/> tag adding class if there are no chords in line(no-chords) so we can reduce lineheight
		 */
		//$text = '<songline>' . str_replace("\n", '</songline><songline>', $text) . '<songline>'; -- need to put at least &nbsp; in empty line
		$lines = explode("\n", $text);
		$lineHtml = [];
		foreach($lines as $i => $line) {
			$lineClass = [];
			if($line) {
				//is this an empty line
				if(ctype_cntrl($line)) {
					$line = '&nbsp;';
					$lineClass[] = "empty";
				}
				//do we have chords in this line
				if(stripos($line, '</chords>')!==false) {
					$lineClass[] = "has-chords";
				} else {
					$lineClass[] = "no-chords";
				}
				$lineHtml[] = "\n";
				$lineHtml[] = '<songline data-number="'.$i.'" class="'.implode(" ", $lineClass).'">';
				$lineHtml[] = $line;
				$lineHtml[] = '</songline>';
			}
		}
		$markupText = implode("", $lineHtml);

		//build answer object
		$answer = new \stdClass();
		$answer->originalText = $songText;
		$answer->markUp = $markupText;
		//$answer->chords = (isset($chords)?$chords:false);//there would be no use of this
		$answer->idenfifiedChords = (isset($identifiedChords)?$identifiedChords:[]);
		return($answer);
	}


	/**
	 * Get Chord Type select options - todo: why is this not in the repo?
	 * @param bool|string $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_ChordTypes($zeroOption=false) {
		$lst = [];
		if($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '', \JText::_($zeroOption), 'value', 'text' );
		}
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		/** @var ChordTypeRepository $repo */
		$repo = $em->getRepository('MySongBooks\Core\Entity\ChordType');
		$items = $repo->getSelectList();
		foreach($items as $item) {
			$lst[] = \JHTML::_('select.option', $item["id"], $item["name"], 'value', 'text' );
		}
		return ($lst);
	}


	/**
	 * @param bool|string $zeroOption
	 * @param string $noteNamingScheme
	 * @return array
	 */
	public static function getSelectOptions_ChromaticScaleC($zeroOption=false, $noteNamingScheme='') {
		$lst = [];
		if ($zeroOption!==false) {
			$lst[] = \JHTML::_('select.option',  '', \JText::_($zeroOption), 'value', 'text' );
		}
		$CSCA = self::getChromaticScaleC($noteNamingScheme);
		foreach($CSCA as $k => $v) {
			$lst[] = \JHTML::_('select.option', $k, $v, 'value', 'text');
		}
		return ($lst);
	}

	/**
	 * @param int $noteNumber
	 * @param string $noteNamingScheme
	 * @return string
	 */
	public static function getNoteLetterForChromaticScaleC($noteNumber, $noteNamingScheme='') {
		$CSCA = self::getChromaticScaleC($noteNamingScheme);
		return(isset($CSCA[$noteNumber])?$CSCA[$noteNumber]:"?");
	}

	/**
	 * The system config option "default_note_naming" can be "C" or "Do"
	 * @param string $noteNamingScheme
	 * @return array
	 */
	public static function getChromaticScaleC($noteNamingScheme='') {
		if(empty($noteNamingScheme) || !array_key_exists($noteNamingScheme, self::$chromatic_scale_notes)) {
			$noteNamingScheme = CPH::getOption("default_note_naming");
		}
		return(self::$chromatic_scale_notes[$noteNamingScheme]);
	}
}