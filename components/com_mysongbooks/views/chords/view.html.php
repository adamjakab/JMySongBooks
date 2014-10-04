<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */
defined('_JEXEC') or die;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;

/**
 * Class MySongBooksViewChords
 */
class MySongBooksViewChords extends \JViewLegacy {
	/** @var  JController */
	private $_ctrl = null;

	/** @var string  */
	protected $context;

	/**
	 * @param array $config
	 */
	public function __construct($config=[]) {
		parent::__construct($config);
		$this->context = strtolower(CPH::getOption("com_name") . '.' . CPH::getOption("controller"));
	}

	/**
	 * @param null $tpl
	 * @return mixed|void
	 */
	public function display($tpl = null) {
		$root_note = \JFactory::getApplication()->getUserStateFromRequest($this->context.'.root_note', "root_note", false);
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		parent::display($tpl);
	}

	/**
	 * Stores the reference of the current controller set by JController::display
	 * @param JController $controller
	 */
	public function setController($controller) {
		$this->_ctrl = $controller;
	}
}
