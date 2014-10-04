<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
use MySongBooks\Core\Joomla\JView;
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Helpers\ComponentParamHelper AS CPH;

/**
 * To load this view use layout=json
 * Class MySongBooksViewJsonConfigurations
 */
class MySongBooksViewJsonConfigurations extends JView {
    /**
     * @param array $config
     */
    function __construct($config=[]) {
        parent::__construct($config);
	    header('Content-type: application/json; charset=utf-8');
    }

	/**
	 * Outputs the parameter type specific edit form
	 */
	public function getParamEditForm() {
	    $answer = new stdClass();
	    $JI = \JFactory::getApplication()->input;
	    $paramName = $JI->getString("paramName", null);
	    try {
		    $answer->result = CPH::getParamEditForm($paramName) ;
	    } catch (Exception $e) {
		    $answer->errors = $e->getErrorObject();
	    }
	    echo json_encode($answer);
	    \JFactory::getApplication()->close();
    }

	/**
	 * Stores the parameter value set by user
	 */
	public function submitParamEditForm() {
		$answer = new stdClass();
		$JI = \JFactory::getApplication()->input;
		$paramName = $JI->getString("paramName", null);
		$paramValue = $JI->getString("paramValue", null);
		try {
			$answer->result = CPH::submitParamEditForm($paramName, $paramValue);
		} catch (Exception $e) {
			$answer->errors = $e->getErrorObject();
		}
		echo json_encode($answer);
		\JFactory::getApplication()->close();
	}
}