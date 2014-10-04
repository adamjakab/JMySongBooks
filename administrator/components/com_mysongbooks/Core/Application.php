<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core;
defined('_JEXEC') or die();
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper;

if (!class_exists('MySongBooks\Core\Application')) {
    /**
     * Class Application
     */
    class Application {
	    /**
	     * Main Application construct
	     * @param string $defaultControllerName
	     */
	    public function __construct($defaultControllerName="cp") {
			CPH::setOption('com_name', COMPONENT_ELEMENT_NAME_MYSONGBOOKS);
			CPH::setOption('com_path_admin', dirname(__DIR__));
			CPH::setOption('com_path_site', str_replace('/administrator','', CPH::getOption('com_path_admin')));
			CPH::setOption('com_uri_admin', str_replace(JPATH_ROOT, '', CPH::getOption('com_path_admin')));
			CPH::setOption('com_uri_site', str_replace(JPATH_ROOT, '', CPH::getOption('com_path_site')));

			//CONTROLLER/TASK/VIEW
		    $JI = new \JInput;
		    $ctrl = $JI->getCmd('view', $defaultControllerName);
		    $task = $JI->getCmd('task', 'display');
		    $format = $JI->getCmd('format', '');
			if ($task && strpos($task, '.') !== false) {
				$parts = explode(".", $task);
				$ctrl = $parts[0];
				$task = $parts[1];
			}
			$view = $ctrl;
			CPH::setOption('ctrl.task', $ctrl . '.' . $task);
			CPH::setOption('controller', $ctrl);
			CPH::setOption('task', $task);
			CPH::setOption('view', $view);
			CPH::setOption('format', $format);
		}

	    /**
	     * Initialize Application and execute controller
	     * @param null $componentLocation
	     */
	    public function init($componentLocation = null) {
            //component execution location
			$componentLocation = (in_array($componentLocation, ["frontend", "backend"]) ? $componentLocation : 'frontend');
			CPH::setOption('com_location', $componentLocation);


		    $JI = new \JInput;
		    $JI->set("task", CPH::getOption("controller") . '.' . CPH::getOption("task"));
		    $JI->set("view", CPH::getOption("view"));


		    //set header js/css assets
			InterfaceHelper::setDefaultHeaderIncludes();

		    //Set up Doctrine and acquire Entity Manager - using custom jdoctrine library
		    require_once JPATH_ROOT."/libraries/jdoctrine/jdoctrine.php";
		    $JDO = new \stdClass();
		    $JDO->configuration = new \stdClass();
		    $JDO->configuration->type = "annotation";
		    $JDO->configuration->paths = [JPATH_ROOT."/administrator/components/".COMPONENT_ELEMENT_NAME_MYSONGBOOKS."/Core/Entity"];
		    $JDO->configuration->isDevMode = true;
		    $JDO->connection = null;
		    $JDO->eventManager = null;
		    $em = \JDoctrine::getEntityManager($JDO);
		    CPH::setOption('EntityManager', $em);

			//Get and run the controller
			try {
				$controller = \JControllerAdmin::getInstance(COMPONENT_INSTANCE_NAME_MYSONGBOOKS.ucfirst(CPH::getOption("format")));
				if ($controller) {
					$controller->execute(CPH::getOption('task'));
					$controller->redirect();
				}
			} catch (Exception $e) {
				if(class_exists("JToolBarHelper")) {
					\JToolBarHelper::title(\JText::sprintf("MYSONGBOOKS_CORE_EXCEPTION", COMPONENT_INSTANCE_NAME_MYSONGBOOKS));
				}
				echo($e->getFormattedErrorMessage());
			} catch (\Exception $e) {
				if(class_exists("JToolBarHelper")) {
					\JToolBarHelper::title(\JText::sprintf("MYSONGBOOKS_CORE_EXCEPTION", COMPONENT_INSTANCE_NAME_MYSONGBOOKS));
				}
				echo($e->getMessage());
			}
		}
	}
}
