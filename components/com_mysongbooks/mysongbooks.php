<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */
defined('_JEXEC') or die;

//load definitions and classLoader
$incPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'Core'.DS.'Includes';
try {
	if(file_exists($incPath.DS.'defines.php')) {
		require_once ($incPath.DS.'defines.php');
	} else {
		throw new \Exception("Unable to load: " . $incPath.DS.'defines.php');
	}
	if(file_exists($incPath.DS.'loader.php')) {
		require_once($incPath.DS.'loader.php');
	} else {
		throw new \Exception("Unable to load: " . $incPath.DS.'loader.php');
	}
} catch(\Exception $e) {
	\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
	return;
}

//create and init the app
$coreApp = new MySongBooks\Core\Application("profile");
$coreApp->init("frontend");
