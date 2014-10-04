<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Entity;
defined('_JEXEC') or die();
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

/**
 * Class ChordType
 * @Entity(repositoryClass="MySongBooks\Core\Repository\ChordTypeRepository")
 * @Table(name="mysongbooks_chord_type",
 *      uniqueConstraints={
 *          @UniqueConstraint(name="idx_abbreviation", columns={"abbreviation"})
 *      }
 * )
 */
class ChordType extends JEntity implements JEntityInterface {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string", length=16)
	 * @var string
	 */
	protected $abbreviation;

	/**
	 * @Column(type="json_array", length=255)
	 * @var array
	 */
	protected $alt_abbreviations;

	/**
	 * @Column(type="string", length=32)
	 * @var string
	 */
	protected $name;

	/**
	 * [1=Major, 2=Minor, 3=Diminished, 4=Augmented, 5=Suspended]
	 * @Column(type="smallint", length=3)
	 * @var int
	 */
	protected $type;

	/**
	 * @Column(type="smallint", length=3, options={"default"=1})
	 * @var int
	 */
	protected $published;

	/**
	 * @OneToMany(targetEntity="Chord", mappedBy="type", cascade={"persist"})
	 * @var ArrayCollection
	 */
	protected $type_chords;



	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->type_chords = new ArrayCollection();
		$this->published = 1;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $abbreviation
	 */
	public function setAbbreviation($abbreviation) {
		$this->abbreviation = $abbreviation;
	}

	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}

	/**
	 * @param array $alt_abbreviations
	 */
	public function setAltAbbreviations($alt_abbreviations) {
		$this->alt_abbreviations = $alt_abbreviations;
	}

	/**
	 * @return array
	 */
	public function getAltAbbreviations() {
		return $this->alt_abbreviations;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
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
	 * @return ArrayCollection
	 */
	public function getTypeChords() {
		return($this->type_chords);
	}

	/**
	 * @return int
	 */
	public function countTypeChords() {
		return($this->type_chords->count());
	}

}