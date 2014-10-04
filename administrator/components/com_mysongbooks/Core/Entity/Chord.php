<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Entity;
defined('_JEXEC') or die();
use MySongBooks\Core\Repository\ChordTypeRepository;
use MySongBooks\Core\Joomla\JEntity;
use MySongBooks\Core\Joomla\JEntityInterface;
use MySongBooks\Core\Exception\ValidationException;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\MusicHelper;
use Doctrine\Common\Collections\ArrayCollection;
//Mapping Annotation Classes
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Class Chord
 * @Entity(repositoryClass="MySongBooks\Core\Repository\ChordRepository")
 * @Table(name="mysongbooks_chord",
 *      uniqueConstraints={
 *          @UniqueConstraint(name="idx_root_note_type", columns={"root_note", "chord_type_id"})
 *      },
 *      indexes={}
 * )
 */
class Chord extends JEntity implements JEntityInterface {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * Using numeric notation of semitone positions starting from C===1 until B===12
	 * C C# D D# E F F# G G# A A# B
	 * @Column(type="smallint", length=2, nullable=false)
	 * @var int
	 */
	protected $root_note;

	/**
	 * @Column(type="smallint", length=3, options={"default"=1})
	 * @var int
	 */
	protected $published;

	/**
	 * @ManyToOne(targetEntity="ChordType", inversedBy="type_chords")
	 * @JoinColumn(name="chord_type_id", referencedColumnName="id", nullable=false)
	 * @var ChordType
	 */
	protected $type;

	/**
	 * @OneToMany(targetEntity="ChordLayout", mappedBy="chord", cascade={"persist", "remove"})
	 * @var ArrayCollection
	 */
	protected $chord_layouts;



	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->chord_layouts = new ArrayCollection();
		$this->alt_names = [];
		$this->published = 1;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $root_note
	 * @throws ValidationException
	 */
	public function setRootNote($root_note) {
		if($root_note>=1 && $root_note<=12) {
			$this->root_note = $root_note;
		} else {
			throw new ValidationException("Root note must be a number in between 1 and 12!");
		}
	}

	/**
	 * @return int
	 */
	public function getRootNote() {
		return $this->root_note;
	}

	/**
	 * @return int
	 */
	public function getPublished() {
		return($this->published);
	}

	/**
	 * @param int $published
	 * @throws ValidationException
	 */
	public function setPublished($published) {
		if($published==0||$published==1||$published==2||$published==-2) {
			$this->published = $published;
		} else {
			throw new ValidationException("Bad published value($published)!");
		}
	}

	/**
	 * In order to be able to use JEntity::mapDataArrayOnEntity which maps posted data on entities,
	 * we must allow type to be set by value here
	 * @param ChordType|int $type
	 * @throws ValidationException
	 */
	public function setType($type) {
		if(!is_object($type)) {// && get_class($type) != 'MySongBooks\Core\Entity\ChordType'
			$typeId = (int)$type;
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var ChordTypeRepository $repo */
			$repo = $em->getRepository('MySongBooks\Core\Entity\ChordType');
			/** @var ChordLayout $entity */
			$type = $repo->find($typeId);
		}
		if($type) {
			$this->type = $type;
		} else {
			throw new ValidationException("Type cannot be set by this value($type)!");
		}
	}

	/**
	 * @return ChordType
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Push a ChordLayout in the collection - !!! check ChordLayout::setChord - it calls this automatically
	 * @param ChordLayout $chordLayout
	 */
	public function addChordLayout($chordLayout) {
		$this->chord_layouts[] = $chordLayout;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getChordLayouts() {
		return($this->chord_layouts);
	}

	/**
	 * @return int
	 */
	public function countChordLayouts() {
		return($this->chord_layouts->count());
	}


	/*----------------------------------------------------------------------------------------------------------------*/
	/**
	 * @return string
	 */
	public function getName() {
		return $this->getRootNoteName() . $this->type->getAbbreviation();
	}

	/**
	 * @return int
	 */
	public function getRootNoteName() {
		return MusicHelper::getNoteLetterForChromaticScaleC($this->root_note);
	}

	/**
	 * @return string
	 */
	public function getAlternativeNames() {
		$answer = "";
		$typeAbbreviations = $this->type->getAltAbbreviations();
		if(count($typeAbbreviations)) {
			foreach($typeAbbreviations as &$abbr) {
				$abbr = $this->getRootNoteName() . $abbr;
			}
			$answer .= implode(", ", $typeAbbreviations);
		}
		return($answer);
	}

	/**
	 * Returns something like: Do7+(Domaj7, C7+, Cmaj7)
	 * @return string
	 */
	public function getAllNames() {
		$altNames = $this->getAlternativeNames();
		return($this->getName() . (!empty($altNames) ? "(" . $altNames . ")" : ""));
	}
}

