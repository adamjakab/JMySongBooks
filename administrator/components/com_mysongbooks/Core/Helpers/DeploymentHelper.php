<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die();
use MySongBooks\Core\Helpers\ComponentParamHelper;
use MySongBooks\Core\Helpers\DatabaseUpdaterHelper;


/**
 * Class DeploymentHelper
 */
class DeploymentHelper {
	/** @var array */
	private static $messages = [];

	/** @var string */
	private static $installPath;


	/**
	 * pass some params here
	 */
	public static function setup($installPath) {
		self::$installPath = $installPath;
	}


	/**
	 * Executed on: install, update, discover_install
	 *
	 * @param string $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return boolean - returning false will halt the execution
	 */
	public static function preflight($type, $parent) {
		$answer = self::checkJDoctrineDependency();
		return($answer);
	}


	/**
	 * Executed on: install
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public static function install($parent) {
		$answer = true;
		self::addMessage(($answer?"success":"error"), "Component Install");
		return($answer);
	}


	/**
	 * Executed on: update
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public static function update($parent) {
		$answer = true;
		self::addMessage(($answer?"success":"error"), "Component Update");
		return($answer);
	}

	/**
	 * Executed on: install, update, discover_install
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param string $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public static function postflight($type, $parent) {
		$answer = self::recheckConfiguration();
		self::addMessage(($answer?"success":"error"), "Post-check(configuration checks)");
		$answer = self::executeDatabaseUpdater();
		self::addMessage(($answer?"success":"error"), "Post-check(database updater)");
		$answer = self::setupFilesAndFolders();
		self::addMessage(($answer?"success":"error"), "Post-check(files/folders)");
		//
		self::dumpMessages();
	}

	/**
	 * Executed on: uninstall
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public static function uninstall($parent) {
		$answer = self::removeDatabaseTables();
		self::addMessage(($answer?"success":"error"), "Component Uninstall");
		//
		self::dumpMessages();
	}

	//---------------------------------------------------------------------------------------------------PRIVATE METHODS

	/**
	 * (PREFLIGHT) - JDoctrine dependency check
	 * This component is dependent on JDoctrine library so if it is missing then installation must halt
	 * @return bool
	 */
	private static function checkJDoctrineDependency() {
		$answer = file_exists(JPATH_ROOT."/libraries/jdoctrine/jdoctrine.php");
		if(!$answer) {
			\JFactory::getApplication()->enqueueMessage("JDoctrine dependency check failed! Please install JDoctrine(http://devshed.jakabadambalazs.com) library first.","error");
		}
		return($answer);
	}


	/**
	 * (POSTFLIGHT) - Rechecks configuration options and adds default configuration values where missing
	 * @return bool
	 */
	private static function recheckConfiguration() {
		$answer = true;
		ComponentParamHelper::recheckConfigurationAndSetDefaultConfiguration();
		return($answer);
	}

	/**
	 * (POSTFLIGHT) - Updates database
	 * @return bool
	 */
	private static function executeDatabaseUpdater() {
		$DBUH = new DatabaseUpdaterHelper();
		$answer = $DBUH->update(false);//not verbose
		return($answer);
	}

	/**
	 * (POSTFLIGHT) - Do stuff to files and folders
	 * @return bool
	 */
	private static function setupFilesAndFolders() {
		$answer = true;
		return($answer);
	}


	/**
	 * (UNINSTALL) - remove database tables
	 */
	private static function removeDatabaseTables() {
		$answer = true;
		self::addMessage("info", "No database tables were not removed.");
		return($answer);
	}



	//---------------------------------------------------------------------------------------------------OUTPUT/MESSAGES
	private static function dumpMessages() {
		$html = '';
		if(count(self::$messages)) {
			$html .= '<table class="table table-bordered">';
			foreach(self::$messages as $ma) {
				$color = 'transparent';
				if($ma["type"]=="success") {$color="#589d56";}
				if($ma["type"]=="warning") {$color="#F2B876";}
				if($ma["type"]=="error") {$color="#FF2B40";}
				$html .= '<tr>';
				$html .= '<td style="min-width:250px; border-bottom:1px solid #bababa;">'.$ma["message"].'</td>';
				$html .= '<td style="background-color:'.$color.'; border-bottom:1px solid #bababa; text-align:center;">'.$ma["type"].'</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		echo $html;
		self::$messages = [];
	}

	/**
	 * @param string $type
	 * @param string $message
	 */
	private static function addMessage($type, $message) {
		if (in_array($type, ["info", "success", "warning", "error"])&&!empty($message)) {
			array_push(self::$messages, ["type"=>$type, "message"=>$message]);
		}
	}
}

