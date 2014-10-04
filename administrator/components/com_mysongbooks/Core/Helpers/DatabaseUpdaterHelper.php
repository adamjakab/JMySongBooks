<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die();
use MySongBooks\Core\Exception\Exception;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class DatabaseUpdaterHelper
 */
class DatabaseUpdaterHelper {
    /** @var boolean  */
	private $verbose = true;
	/** @var string */
	private $entityFolder = null;
	/** @var string */
	private $entityNamespace = null;
	/** @var array */
	private $entityList = [];


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->entityNamespace = '\\' .COMPONENT_INSTANCE_NAME_MYSONGBOOKS .'\Core\Entity';
		$this->entityFolder = realpath(dirname(__DIR__).DS.'Entity');
	}

	/**
	 * @param bool $verbose
	 * @return bool
	 */
	public function update($verbose = false) {
		$answer = false;
		$this->verbose = $verbose;
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = $this->obtainDevModeEntityManager();
		if(!get_class($em)=='Doctrine\ORM\EntityManager') {
			$this->log("EntityManager is not available!");
		} else {
			$this->log("Starting entity alignment...");
			$this->setupEntityList();
			$this->log("Found entities: " . json_encode($this->entityList));
			$classes = [];
			foreach($this->entityList as $entityName) {
				$classes[] = $em->getClassMetadata($this->entityNamespace.'\\'.$entityName);
			}
			$schemaTool = new SchemaTool($em);
			$schemaTool->updateSchema($classes, true);
			$answer = true;
		}
		return($answer);
	}

	/**
	 * Non-caching EntityManager
	 * @return \Doctrine\ORM\EntityManager|bool
	 */
	private function obtainDevModeEntityManager() {
		require_once JPATH_ROOT."/libraries/jdoctrine/jdoctrine.php";
		$JDO = new \stdClass();
		$JDO->configuration = new \stdClass();
		$JDO->configuration->type = "annotation";
		$JDO->configuration->paths = [JPATH_ROOT."/administrator/components/".COMPONENT_ELEMENT_NAME_MYSONGBOOKS."/Core/Entity"];
		$JDO->configuration->isDevMode = true;
		$JDO->connection = null;
		$JDO->eventManager = null;
		return(\JDoctrine::getEntityManager($JDO));
	}

	/**
	 * Set list of entities to be worked on
	 * This assumes that file name === class name (MyEntity.php is class MyEntity ... makes sense)
	 */
	private function setupEntityList() {
		$EFLIST = $this->getFileList($this->entityFolder, '/^[a-z0-9_]*\.php$/i');
		if (count($EFLIST)) {
			foreach ($EFLIST as $entityFile) {
				$entityName = preg_replace(['/\.php/'], [''], $entityFile);
				if(class_exists($this->entityNamespace.'\\'.$entityName)) {
					array_push($this->entityList, $entityName);
				}
			}
		}
	}

	/**
	 * @param string $dir
	 * @param string $file_pattern
	 * @return array
	 */
	private function getFileList($dir, $file_pattern = '/.*/') {
		$answer = [];
		if (($handle = opendir($dir))) {
			while (false !== ($file = readdir($handle))) {
				if (preg_match($file_pattern, $file) == 1) {
					$answer[] = $file;
				}
			}
			closedir($handle);
		}
		if (count($answer) > 0) {
			sort($answer);
		}
		return ($answer);
	}

	/**
	 * @param string $msg
	 * @param string $type
	 */
	private function log($msg, $type = "info") {
		if ($this->verbose) {
			echo '<br /><span class="' . $type . '">' . $msg . '</span>';
		}
	}
}

?>