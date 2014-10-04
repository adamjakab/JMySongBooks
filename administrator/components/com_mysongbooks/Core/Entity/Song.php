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
 * Class Song
 * @Entity(repositoryClass="MySongBooks\Core\Repository\SongRepository")
 * @Table(name="mysongbooks_song",
 *      uniqueConstraints={
 *          @UniqueConstraint(name="idx_title", columns={"title"})
 *      },
 *      indexes={}
 * )
 */
class Song extends JEntity implements JEntityInterface {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string", length=196)
	 * @var string
	 */
	protected $title;

	/**
	 * @Column(type="string", length=128)
	 * @var string
	 */
	protected $author;

	/**
	 * Name of where song is coming from (book/collection/etc)
	 * @Column(type="string", length=64)
	 * @var string
	 */
	protected $origin;

	/**
	 * Page or numbering reference in the origin
	 * @Column(type="string", length=8)
	 * @var string
	 */
	protected $origin_reference;

	/**
	 * Any notes found in the origin worth to note and/or copyright notes
	 * @Column(type="string", length=512)
	 * @var string
	 */
	protected $origin_note;

	/**
	 * @Column(type="text", length=65535) ~64Kb - MySql type = TEXT
	 * @var string
	 */
	protected $content;

	/**
	 * @Column(type="smallint", length=3, options={"default"=1})
	 * @var int
	 */
	protected $published;



	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->published = 1;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	 * @param string $title
	 * @throws ValidationException
	 */
	public function setTitle($title) {
		if(strlen($title)<3) {
			throw new ValidationException("Title must be at least 3 chars long!");
		}
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param string $origin
	 */
	public function setOrigin($origin) {
		$this->origin = $origin;
	}

	/**
	 * @return string
	 */
	public function getOrigin() {
		return $this->origin;
	}

	/**
	 * @param string $origin_note
	 */
	public function setOriginNote($origin_note) {
		$this->origin_note = $origin_note;
	}

	/**
	 * @return string
	 */
	public function getOriginNote() {
		return $this->origin_note;
	}

	/**
	 * @param string $origin_reference
	 */
	public function setOriginReference($origin_reference) {
		$this->origin_reference = $origin_reference;
	}

	/**
	 * @return string
	 */
	public function getOriginReference() {
		return $this->origin_reference;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}




}