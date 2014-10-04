<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
defined('_JEXEC') or die();

use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Exception\ValidationException;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper;


/**
 * Class JView
 * @package MySongBooks\Core\Joomla
 */
class JView extends \JViewLegacy {
	/** @var  JController */
	private $_ctrl = null;

	/** @var array - the properties of the item currently being edited */
	private $editData;

	/** @var \stdClass */
	protected $stateData;

	/** @var string */
	protected $context;

	/** @var JRepository */
	protected $repository;

	/** @var string - by default listing will be ordered by this column */
	protected $default_order_column = 'i.id';

	/** @var string - by default listing will be ordered in this direction (ASC|DESC) */
	protected $default_order_direction = 'ASC';

	/** @var integer(10|25|50|100|0===ALL) */
	protected $default_paging_limit = 25;

	/** @var integer - number of filtered items in listing  */
	protected $count_filtered;

	/** @var integer - number of unfiltered items in listing(total number of items)  */
	protected $count_unfiltered;



	/**
	 * @param array $config
	 */
	function __construct($config=[]) {
		parent::__construct($config);
		$this->context = strtolower(CPH::getOption("com_name") . '.' . CPH::getOption("controller"));
		$this->registerStateData();
    }

	/**
	 * Default view display method
	 * @param null $tpl
	 * @return mixed|void
	 * @throws \MySongBooks\Core\Exception\Exception
	 */
	public function display($tpl = null) {
		$this->countItems();
		InterfaceHelper::setHeaderTitle(CPH::getOption('controller'));
		parent::display($tpl);
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode('<br />', $errors), 500);
		}
	}

	/**
	 * Returns array of Entities from the repository
	 * @return array
	 */
	public function getItems() {
		$answer = $this->repository->getFilteredItems($this->getStateData(), $this->getFilterDefinitions());
		return($answer);
	}


	/**
	 * count items and register counts
	 */
	private function countItems() {
		if($this->repository) {
			$this->count_filtered = $this->repository->countFilteredItems($this->getStateData(), $this->getFilterDefinitions());
			$this->count_unfiltered = $this->repository->countFilteredItems(json_decode('{"filters":[],"list":[]}'), $this->getFilterDefinitions());
		}
	}


	/**
	 * Returns Html for filter tools above list of items
	 * @return string
	 */
	public function getFilters() {
		$options = [
			"totalRecords"=>$this->count_unfiltered,
			"filteredRecords"=>$this->count_filtered
		];
		return(InterfaceHelper::getFilterBarForView($this->getFilterDefinitions(), $options));
	}


	/**
	 * Returns pagination stepper
	 * @return \JPagination
	 */
	public function getPagination() {
		$stateData = $this->getStateData();
		$total = $this->count_filtered;
		$start = (isset($stateData->list["limitstart"])?$stateData->list["limitstart"]:0);
		$limit = (isset($stateData->list["limit"])?$stateData->list["limit"]:25);
		$page = new \JPagination($total, $start, $limit);
		return($page);
	}


	/**
	 * Edit selected entity
	 * @param array $formData - formData modified by extending view class for special cases
	 */
	public function edit($formData=null) {
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		/** @var array $formData */
		$formData = ($formData?$formData:$JI->post->get('jform', [], 'array'));
		if(!isset($formData["id"])||empty($formData["id"])) {
			$formData["id"] = $JI->getInt('cid', 0);
		}
		//echo "<pre>" . print_r($formData, true) . "</pre>";
		$this->editData = $this->repository->getEditDataArray($formData);
		//echo "<pre>" . print_r($this->editData, true) . "</pre>";
	}


	/**
	 * Returns value of specific propery from data of item currently being edited
	 * Called by edit form view template
	 * @param string $propName
	 * @param string $noDataValue - value to use when there is no editData value
	 * @return string
	 */
	public function getEditDataForProp($propName, $noDataValue="") {
		return(isset($this->editData[$propName])?$this->editData[$propName]:$noDataValue);
	}


	/**
	 * Saves the entity being edited
	 * @throws ValidationException
	 */
	public function save() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$formData = $JI->post->get('jform', [], 'array');
		//echo "<pre>formData: " . json_encode($formData) . "</pre>";

		try {
			$entity = $this->repository->setupEntityByData($formData);
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			$em->persist($entity);
			$em->flush();
			if(CPH::getOption('task')=="apply") {
				$JI->set('cid', $entity->getId());
				$this->edit();
			} else {
				$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display', false));
			}
		} catch(ValidationException $e) {
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
			$this->edit();
		}
	}
	/** Just a shortcut */
	public function apply() {$this->save();}


	/**
	 * Deletes a trashed item
	 */
	public function delete() {
		\JSession::checkToken() or jexit(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->post->get('cid', [], 'array');
		$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display', false));
		if(!(is_array($cid) && count($cid))) {
			\JFactory::getApplication()->enqueueMessage("No item is selected for deletion!","warning");
			return;
		}
		$entities = $this->repository->getEntitiesById($cid);
		if (count($entities)) {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var JEntityInterface $entity */
			foreach($entities as $entity) {
				try {
					if($entity->getPublished() == -2) {
						$em->remove($entity);
					} else {
						\JFactory::getApplication()->enqueueMessage("This item(id=".$entity->getId().") is not trashed so it cannot be deleted!", "warning");
					}
				} catch (Exception $e) {
					\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
				}
			}
			$em->flush();
		}
	}

	/**
	 * Catch-all method for publish, unpublish, archive, trash
	 */
	public function publish() {
		\JSession::checkToken() or die(\JText::_('MYSONGBOOKS_INVALID_TOKEN'));
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get('cid', [], 'array');
		$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display', false));
		if(!(is_array($cid) && count($cid))) {
			\JFactory::getApplication()->enqueueMessage("No item is selected for state change!","warning");
			return;
		}
		$publishStates = ['publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3 ];
		$task = CPH::getOption("task");
		$publish = \JArrayHelper::getValue($publishStates, $task, 0, 'int');
		$entities = $this->repository->getEntitiesById($cid);
		if (count($entities)) {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var JEntityInterface $entity */
			foreach($entities as $entity) {
				try {
					$entity->setPublished($publish);
					$em->persist($entity);
				} catch (ValidationException $e) {
					//
				} catch (Exception $e) {
					\JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
				}
			}
			$em->flush();
		}
	}
	/** Shortcuts for publish */
	public function unpublish() {$this->publish();}
	public function archive() {$this->publish();}
	public function trash() {$this->publish();}


	/**
	 * Returns the filter definitions used by the filter bar
	 * Here we will be returning the three most used filters: search(free), limits(pagination limits), published(state filter)
	 * This method can/should be overwritten by extending class to add controller specific filters
	 * Note: If whereSql key is found in definition, JRepository will attempt to add it
	 * as a WHRERE condition to the query being built - Check in JREpository::applyFiltersToQueryBuilder
	 * whereSql -> the value should be rappresented with "?", like: "i.name = ?" or more complex "(i.name = ? OR i.alias = ?)"
	 * whereParam -> use this if the value to be put in the whereSql statement above is any different from a single value (???eh?)
	 * For example the sql "i.name LIKE '%something%'" should be rappresented by:
	 *      "whereSql" => "i.name LIKE ?",
	 *  	"whereParam" => "%?%",
	 * @return \stdClass
	 */
	public function getFilterDefinitions() {
		$answer = new \stdClass();
		$SD = $this->getStateData();

		//SEARCH(most probably you will need to override the whereSql in specific views)
		$answer->search = [
			"name" => "search",
			"type" => "text",
			"whereSql" => "i.title LIKE ?",
			"whereParam" => "%?%",
			"value" => (isset($SD->filters["search"])?$SD->filters["search"]:""),
			"placeholder" => \JText::_("MYSONGBOOKS_SEARCH")
		];

		//LIMITS
		$answer->limits = [
			"name" => "limits",
			"type" => "list",
			"value" => (isset($SD->list["limit"])?$SD->list["limit"]:25),
			"options" => [
				"10" => "10",
				"25" => "25",
				"50" => "50",
				"100" => "100",
				"0" => "All",
			]
		];

		//PUBLISHED STATE
		$answer->published = [
			"name" => "published",
			"type" => "select",
			"value" => (isset($SD->filters["published"])?$SD->filters["published"]:""),
			"whereSql" => "i.published = ?",
			"options" => InterfaceHelper::getSelectOptionsPublishedStates(\JText::_("MYSONGBOOKS_SELECT_STATUS"))
		];

		return($answer);
	}

	/**
	 * Returns StateData
	 * @return \stdClass|bool
	 */
	public function getStateData() {
		return($this->stateData);
	}

	/**
	 * Gets posted StateData merges with session stored StateData,
	 * re-stores it in session and sets it in $this->stateData for later use
	 * NOTE: StateData is referred mostly to filter data
	 */
	private function registerStateData() {
		$app = \JFactory::getApplication();
		$SD = new \stdClass();

		//FILTERS: fileter name/value pairs
		$SD->filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', [], 'array');

		//LIST: ordering(ordering, direction) and limits(limit, limitstart)
		$SD->list = [];

		/** @var array $userStateList */
		$userStateList = $app->getUserState($this->context . '.list', []);

		/** @var array $userPostList */
		$userPostList = $app->input->get("list", [], 'array');

		//ordering & direction use old style hidden fields outside list group - so we must add them to $userPostList manually
		if(($ordering = $app->input->getString('filter_order', false))) {
			$userPostList["ordering"] = $ordering;
		}
		if(($direction = $app->input->getString('filter_order_Dir', false))) {
			$userPostList["direction"] = $direction;
		}
		/*
		echo '<pre>'
			. 'context: ' . $this->context . "<br />"
			. 'Filters: ' . print_r($SD->filters, true)
			. 'userStateList: ' . print_r($userStateList, true)
			. 'userPostList:' . print_r($userPostList, true)
			. ''
			.'</pre>';
		*/

		if (!isset($userStateList["ordering"]) && !isset($userPostList["ordering"])) {
			$userPostList["ordering"] = $this->default_order_column;
		}
		if (!isset($userStateList["direction"]) && !isset($userPostList["direction"])) {
			$userPostList["direction"] = $this->default_order_direction;
		}


		/** limit start - uses old style hidden field
		 * NOTE: if limit has changed and we have limitstart!==0 (we are on page2/3...) there is a good chance that
		 * we will miss some records and Pagination bar will be hidden meaning that we will not be able to get to those records
		 * so every time limit changes we will set limitstart to zero
		 */
		$oldLimit = (isset($userStateList["limit"])?$userStateList["limit"]:false);
		$currLimit = (isset($userPostList["limit"])?$userPostList["limit"]:false);
		if($currLimit!==false && $currLimit!=$oldLimit) {
			$userPostList["limitstart"] = 0;
		} else {
			$userPostList["limitstart"] = $app->input->getInt('limitstart', (isset($userStateList["limitstart"])?$userStateList["limitstart"]:0));
		}

		//if limit is not set it will result in showing all records from table(not good)
		if($oldLimit===false && $currLimit===false) {
			$userPostList["limit"] = $this->default_paging_limit;
		}

		//MERGE LIST STATE WITH POST
		$SD->list = array_merge($userStateList, $userPostList);

		//STORE LISTS (FILTERS are already stored by getUserStateFromRequest above)
		$app->setUserState($this->context . '.list', $SD->list);

		//SET FOR LATER USE
		$this->stateData = $SD;
	}


    /** Returns the rendered sidemenu
     * @return string
     */
    public function getSidemenu() {
        return(InterfaceHelper::getSidemenu(CPH::getOption('controller')));
    }

	/**
	 * Stores the reference of the current controller set by JController::display
	 * @param JController $controller
	 */
	public function setController($controller) {
		$this->_ctrl = $controller;
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
		    throw new Exception("Controller is not set in JView - cannot set redirect!");
	    }
    }
}
