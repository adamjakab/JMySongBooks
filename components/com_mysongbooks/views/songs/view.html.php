<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */
defined('_JEXEC') or die;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Repository\SongRepository;
use MySongBooks\Core\Entity\Song;
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Exception\ValidationException;
/**
 * Class MySongBooksViewSongs
 */
class MySongBooksViewSongs extends \JViewLegacy {
	/** @var  JControllerLegacy */
	private $_ctrl = null;

	/** @var string  */
	protected $context;

	/** @var string  */
	protected $songPageContent;



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
		//$this->referenceRequest = \JFactory::getApplication()->getUserStateFromRequest($this->context.'.bref', "bref", false);
		parent::display($tpl);
	}


	/**
	 * Show song
	 */
	public function details() {
		parent::display("details");
	}


	/**
	 * Print song - raw component output
	 */
	public function printit() {
		$this->songPageContent = $this->loadTemplate("details");
		$printPageContent = $this->loadTemplate("print");//will include $detailsPageContent in its output
		echo $printPageContent;
		\JFactory::getApplication()->close();
	}


	/**
	 * Edit song
	 */
	public function edit() {
		parent::display("edit");
	}

	/**
	 * Save Song
	 */
	public function save() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$formData = $JI->post->get('jform', [], 'array');
		//echo('<pre>formData: ' . print_r($formData, true) . '</pre>');
		try {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var SongRepository $repo */
			$repo = $em->getRepository('MySongBooks\Core\Entity\Song');
			/** @var Song $entity */
			$entity = $repo->setupEntityByData($formData);
			$em->persist($entity);
			$em->flush();
			$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.details&sid='.$entity->getId(), false));
		} catch(ValidationException $e) {
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
			$this->edit();
		} catch (Exception $e) {
			\JFactory::getApplication()->enqueueMessage(get_class($e).": ".$e->getMessage(), "error");
			$this->edit();
		} catch (\Exception $e) {
			\JFactory::getApplication()->enqueueMessage(get_class($e).": ".$e->getMessage(), "error");
			$this->edit();
		}
	}


	/**
	 * Set the redirect url in the controller
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 * @throws \MySongBooks\Core\Exception\Exception
	 */
	protected function setRedirect($url, $msg=null, $type=null) {
		if($this->_ctrl !== null) {
			$this->_ctrl->setRedirect($url, $msg, $type);
		} else {
			throw new MySongBooks\Core\Exception\Exception("Controller is not set in JView - cannot set redirect!");
		}
	}

	/**
	 * Stores the reference of the current controller set by JController::display
	 * @param JControllerLegacy $controller
	 */
	public function setController($controller) {
		$this->_ctrl = $controller;
	}
}
