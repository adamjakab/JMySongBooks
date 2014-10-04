<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
use MySongBooks\Core\Repository\ChordRepository;
use MySongBooks\Core\Repository\ChordLayoutRepository;
use MySongBooks\Core\Repository\ChordTypeRepository;
use MySongBooks\Core\Joomla\JView;
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\MusicHelper;
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Exception\ValidationException;
use MySongBooks\Core\Entity\ChordLayout;
use MySongBooks\Core\Entity\ChordType;
use MySongBooks\Core\Entity\Chord;

/**
 * Class MySongBooksViewChords
 */
class MySongBooksViewChords extends JView {
	/** @var ChordRepository */
	protected $repository;

	/**
	 * @param array $config
	 */
	public function __construct($config=[]) {
		parent::__construct($config);
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$this->repository = $em->getRepository('MySongBooks\Core\Entity\Chord');
    }


	/**
	 * Returns the filter definitions used by the filter bar
	 * @return \stdClass
	 */
	public function getFilterDefinitions() {
		$FD = parent::getFilterDefinitions();
		$SD = $this->getStateData();
		//REMOVE FREE SEARCH
		unset($FD->search);
		//REMOVE STATE SELECTOR - ALL NOTES ARE PUBLISHED
		unset($FD->published);
		//
		//ROOT NOTE
		$FD->root_note = [
			"name" => "root_note",
			"type" => "select",
			"value" => (isset($SD->filters["root_note"])?$SD->filters["root_note"]:""),
			"whereSql" => "i.root_note = ?",
			"options" => MusicHelper::getSelectOptions_ChromaticScaleC(\JText::_("MYSONGBOOKS_SELECT_ROOT_NOTE"))
		];
		//CHORD TYPE
		$FD->type = [
			"name" => "type",
			"type" => "select",
			"value" => (isset($SD->filters["type"])?$SD->filters["type"]:""),
			"whereSql" => "i.type = ?",
			"options" => MusicHelper::getSelectOptions_ChordTypes(\JText::_("MYSONGBOOKS_SELECT_CHORD_TYPE"))
		];

		return($FD);
	}


	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws Exception
	 */
	public function display($tpl=null) {
		$state = $this->getStateData();
		//echo "<pre>StateData: " . json_encode($state) . "</pre>";
		/*
		$deletebutton = ["core.manage", "chords.trash", 'trash', JText::_('MYSONGBOOKS_BTN_TRASH')];
		if (isset($state->filters["published"]) && $state->filters["published"]==-2) {
			$deletebutton = ["core.manage", "chords.delete", 'delete', JText::_('MYSONGBOOKS_BTN_DELETE')];
		}*/
		IFH::addButtonsToToolBar([
			["core.manage", "chords.createMissingChords", 'loop', JText::_('MYSONGBOOKS_BTN_CREATE_MISSING_CHORDS')],
			/*
			["core.manage", "chords.edit", 'new', JText::_('MYSONGBOOKS_BTN_NEW')],
			["core.manage", "chords.publish", 'publish', JText::_('MYSONGBOOKS_BTN_PUBLISH')],
			["core.manage", "chords.unpublish", 'unpublish', JText::_('MYSONGBOOKS_BTN_UNPUBLISH')],
			["core.manage", "chords.archive", 'archive', JText::_('MYSONGBOOKS_BTN_ARCHIVE')],
			$deletebutton
			*/
		]);
		parent::display($tpl);
	}

	/**
	 * Adds missing chords to database
	 * This really is a temporary method because there should be a view for ChordType where on adding new Types
	 * this should be done automatically
	 * So, this method will already be written so that in future the central part can be moved out from here
	 */
	public function createMissingChords() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		/** @var ChordTypeRepository $chordTypeRepo */
		$chordTypeRepo = $em->getRepository('MySongBooks\Core\Entity\ChordType');
		$chordTypes = $chordTypeRepo->findAll();
		/** @var ChordType $chordType */
		foreach($chordTypes as $chordType) {
			/** -------MOVABLE($chordType)---------- */
			$typeChords = $chordType->getTypeChords();
			for($root_note=1; $root_note<=12; $root_note++) {
				$found = false;
				/** @var Chord $typeChord */
				foreach($typeChords as $typeChord) {
					if($typeChord->getRootNote() == $root_note) {
						$found = true;
						break;
					}
				}
				if(!$found) {
					$newChord = new Chord();
					$newChord->setRootNote($root_note);
					$newChord->setType($chordType);
					//echo "<hr/>Adding new chord(".$chordType->getId()."): ".$newChord->getAllNames();
					$em->persist($newChord);
				}
			}
			$em->flush();
			/** -------MOVABLE($chordType)---------- */
		}
		$this->display();
	}


	/**
	 * @param null $formData
	 */
	public function edit($formData=null) {
		die("---DISABLED---");
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		/** @var array $formData */
		$formData = $JI->post->get('jform', [], 'array');
		$formData = $this->fixSpecialFieldsInFormData($formData);
		parent::edit($formData);
		//Add toolbar butons
		IFH::addButtonsToToolBar([
			["core.manage", "chords.apply", 'apply', 'JTOOLBAR_APPLY', false], /*save&stay*/
			["core.manage", "chords.save", 'save', 'JTOOLBAR_SAVE', false], /*save&close*/
			["core.manage", "chords.display", 'cancel', 'JTOOLBAR_CANCEL', false], /*cancel*/
		]);
		\JFactory::getApplication()->input->set("hidemainmenu",1);//block main-menu
		parent::display("edit");
	}

	/**
	 * Saves the Chord being edited
	 * @throws ValidationException
	 */
	public function save() {
		die("---DISABLED---");
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$formData = $JI->post->get('jform', [], 'array');
		$formData = $this->fixSpecialFieldsInFormData($formData);
		try {
			$entity = $this->repository->setupEntityByData($formData);
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			$em->persist($entity);
			$em->flush();
			if(CPH::getOption('task')=="apply") {
				$JI->set('cid', $entity->getId());
				$this->edit();
			} else {
				$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display', false));
			}
		} catch(ValidationException $e) {
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
			$this->edit();
		} catch (Exception $e) {
			\JFactory::getApplication()->enqueueMessage(get_class($e).": ".$e->getMessage(), "error");
			$this->edit();
		} catch (\Exception $e) {
			\JFactory::getApplication()->enqueueMessage(get_class($e).": ".$e->getMessage(), "error");
			$this->edit();
		}
	}

	/**
	 * Converts/handles special field cases
	 * @param array $formData
	 * @return array
	 */
	private function fixSpecialFieldsInFormData($formData) {
		/*
		 * this has been removed from Chord!
		//alt_names(comma separated list of values -> array)
		if(isset($formData["alt_names"])) {
			$values = $formData["alt_names"];
			$formData["alt_names"] = [];
			if(stripos($values, ",")!==false && count($bits = explode(",", $values))>0) {
				foreach($bits as $bit) {$formData["alt_names"][] = trim($bit);}
			} else {
				$formData["alt_names"][] = $values;
			}
		}
		*/
		//echo "<pre>formData: " . json_encode($formData) . "</pre>";
		return($formData);
	}


	/**
	 * Chord details page
	 */
	public function details() {
		IFH::addButtonsToToolBar([
			["core.manage", "chords.display", 'back', JText::_('MYSONGBOOKS_BACK')],
			["core.manage", "chords.addNewLayout", 'new', JText::_('MYSONGBOOKS_BTN_NEW')],
		]);
		parent::display("details");
	}

	public function addNewLayout() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$chordId = $JI->getInt("id", null);
		/** @var \MySongBooks\Core\Entity\Chord $chord */
		$chord = $this->repository->find($chordId);
		$chordLayout = new ChordLayout();
		$chordLayout->setChord($chord);
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$em->persist($chordLayout);
		$em->flush();
		$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.details&cid='.$chordId, false));
	}

	public function deleteChordlayout() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$chordId = $JI->getInt("id", null);
		$chordLayoutId = $JI->getInt("deleteid", null);
		$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.details&cid='.$chordId, false));
		try {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var ChordLayoutRepository $repo */
			$repo = $em->getRepository('MySongBooks\Core\Entity\ChordLayout');
			/** @var ChordLayout $entity */
			$entity = $repo->find($chordLayoutId);
			$em->remove($entity);
			$em->flush();
		} catch (\Exception $e) {
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
		}
	}

}
