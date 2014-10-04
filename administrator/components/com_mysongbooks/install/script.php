<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;
use MySongBooks\Core\Helpers\DeploymentHelper AS DH;
/**
 * Install scripts for MySongBooks
 *
 * Class com_mysongbooksInstallerScript
 */
class com_mysongbooksInstallerScript {

	/** @var string */
	private $componentName = false;

	/** @var  string */
	private $installPath;


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->setupDeploymentEnvironment();
	}


	/**
	 * Executed on: install, update, discover_install
	 *
	 * @param string $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return boolean - returning false will halt the execution
	 */
	public function preflight($type, $parent) {
		$res = false;
		if($this->componentName) {
			$res = DH::preflight($type, $parent);
		}
		return($res);
	}

	/**
	 * Executed on: install
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public function install($parent) {
		$res = false;
		if($this->componentName) {
			$res = DH::install($parent);
		}
		return($res);
	}


	/**
	 * Executed on: update
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public function update($parent) {
		$res = false;
		if($this->componentName) {
			$res = DH::update($parent);
		}
		return($res);
	}


	/**
	 * Executed on: install, update, discover_install
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param string $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public function postflight($type, $parent) {
		if($this->componentName) {
			DH::postflight($type, $parent);
		}
	}

	/**
	 * Executed on: uninstall
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public function uninstall($parent) {
		if($this->componentName) {
			DH::uninstall($parent);
		}
	}


	/**
	 * We need to require defines.php and loader.php for component from the includes folder
	 * There can be two disctinct cases:
	 * 1) called during installation/update so this script is in an unpacked component folder
	 *  in the "install" folder outside of the component's admin folder
	 * 2) called during uninstall so this script is inside administrator/components/com_name/install
	 *  ie inside the already deployed component
	 */
	private function setupDeploymentEnvironment($type=null) {
		$scriptFolder = realpath(__DIR__);
		$this->installPath = realpath(dirname($scriptFolder));
		//
		if(file_exists($this->installPath."/admin/Core/Includes")) {
			$includesPath = $this->installPath."/admin/Core/Includes";// CASE 1 - install/update
		} else if(file_exists($this->installPath."/Core/Includes")) {
			$includesPath = $this->installPath."/Core/Includes";// CASE 2 - uninstall
		} else {
			echo "<br />Includes path not found!";
			return;
		}
		//
		if(!file_exists($includesPath."/defines.php")) {
			echo "<br />defines.php not found($includesPath)!";
			return;
		}
		require_once($includesPath."/defines.php");
		//
		if(!file_exists($includesPath."/loader.php")) {
			echo "<br />loader.php not found($includesPath)!";
			return;
		}
		require_once($includesPath."/loader.php");
		//
		if (!class_exists('MySongBooks\Core\Helpers\DeploymentHelper')) {
			echo "<br />DeploymentHelper not found!";
			return;
		}
		DH::setup($this->installPath);
		//
		$this->componentName = COMPONENT_ELEMENT_NAME_MYSONGBOOKS;
	}
}
