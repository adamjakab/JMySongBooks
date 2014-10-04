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
use Doctrine\Common\Collections\ArrayCollection;
//Mapping Annotation Classes
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;


/**
 * Class ChordLayout
 * @Entity(repositoryClass="MySongBooks\Core\Repository\ChordLayoutRepository")
 * @Table(name="mysongbooks_chord_layout")
 */
class ChordLayout extends JEntity implements JEntityInterface {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="smallint", length=3, options={"default"=1})
	 * @var int
	 */
	protected $cgroup;

	/**
	 * Scheme should look something like this: 0#4@3/3@3/2@2/0/1@1/0
	 * meaning: (offset)#(finger@fret)/(finger@fret)/(finger@fret)/(finger@fret)/(finger@fret)/(finger@fret)
	 * "0" === "0@0" === empty string
	 * "X" === "X@X" === muted string
	 *
	 * @Column(type="string", length=26)
	 * @var string
	 */
	protected $scheme;

	/**
	 * @Column(type="smallint", length=3, options={"default"=1})
	 * @var int
	 */
	protected $published;

	/**
	 * @ManyToOne(targetEntity="Chord", inversedBy="chord_layouts")
	 * @JoinColumn(name="chord_id", referencedColumnName="id", nullable=false)
	 * @var Chord
	 */
	protected $chord;


	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->published = 1;
		$this->cgroup = 1;
		$this->scheme = '0#x|x|x|x|x|x';
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getCgroup() {
		return $this->cgroup;
	}

	/**
	 * @param int $group
	 * @throws ValidationException
	 */
	public function setCgroup($group) {
		$pattern = "/^[1-3]$/i";
		if(!preg_match($pattern, $group)) {
			throw new ValidationException("Group must be 1 or 2 or 3!");
		}
		$this->cgroup = $group;
	}

	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}


	/**
	 * @param string $scheme
	 * @throws ValidationException
	 */
	public function setScheme($scheme) {
		$pattern = "/^.*$/i";//todo: regex missing for scheme
		if(!preg_match($pattern, $scheme)) {
			throw new ValidationException("Scheme must match pattern: " . $pattern);
		}
		$this->scheme = $scheme;
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
	 * @return Chord
	 */
	public function getChord() {
		return($this->chord);
	}


	/**
	 * Adds this entity to its parent's(Chord) chordLayout collection and then sets the reference to the Chord on this chordLayout
	 * @param Chord $chord
	 */
	public function setChord($chord) {
		$chord->addChordLayout($this);
		$this->chord = $chord;
	}

	//----------------------------------------------------- Other Getters-----------------------------------------

}