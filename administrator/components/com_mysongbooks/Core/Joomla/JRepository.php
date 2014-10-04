<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
defined('_JEXEC') or die();
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;


/**
 * Class JRepository
 */
class JRepository extends EntityRepository {

	/**
	 * Constructor
	 * @param EntityManager $em    The EntityManager to use.
	 * @param ClassMetadata $class The class descriptor.
	 */
	public function __construct($em, ClassMetadata $class) {
		parent::__construct($em, $class);
	}

	/**
	 * Returns filtered array of entities
	 * @param \stdClass $filters
	 * @param \stdClass $filterDefinitions
	 * @return array
	 */
	public function getFilteredItems($filters, $filterDefinitions) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$qb = $em->createQueryBuilder();
		$qb = $this->setupListQuery($qb);
		$qb = $this->applyFiltersToQueryBuilder($qb, $filters, $filterDefinitions);
		$query = $qb->getQuery();
		//echo '<hr /><pre>LISTING: ' .$query->getSQL().'<br />'.print_r('', true).'</pre>';
		$answer = $query->getResult();
		return($answer);
	}

	/**
	 * This method, overriden in the specific Repo, should be adding
	 * SELECTs and JOINs on QB for listing filtered items
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListQuery($qb) {
		return($qb);
	}


	/**
	 * Returns filtered item count
	 * @param \stdClass $filters
	 * @param \stdClass $filterDefinitions
	 * @return integer
	 */
	public function countFilteredItems($filters, $filterDefinitions) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$qb = $em->createQueryBuilder();
		$qb = $this->setupListCountQuery($qb);
		$qb = $this->applyFiltersToQueryBuilder($qb, $filters, $filterDefinitions, true, false, false);
		$query = $qb->getQuery();
		$answer = $query->getSingleScalarResult();
		//echo '<hr /><pre>COUNTING: ' .$query->getSQL().' ::: '.$answer.'</pre>';
		return($answer);
	}

	/**
	 * This method, overriden in the specific Repo, should be adding
	 * SELECTs and JOINs on QB for counting filtered items
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListCountQuery($qb) {
		return($qb);
	}



	/**
	 * @param QueryBuilder $qb
	 * @param \stdClass $filters
	 * @param \stdClass $filterDefinitions
	 * @param boolean $applyFilters - this will be done by specific Repository
	 * @param boolean $applyLimits - if true, will apply record limiting/offsetting as specified in $filters
	 * @param boolean $applyOrdering - if true, will apply record ordering as specified in $filters
	 * @return QueryBuilder
	 */
	protected function applyFiltersToQueryBuilder($qb, $filters, $filterDefinitions, $applyFilters=true, $applyLimits=true, $applyOrdering=true) {
		//FILTERS
		if ($applyFilters) {
			if(isset($filters->filters) && is_array($filters->filters) && count($filters->filters)) {
				if($filterDefinitions && get_class($filterDefinitions)=="stdClass") {
					foreach($filters->filters as $fKey => $fVal) {
						if($fVal!="" && isset($filterDefinitions->$fKey)) {
							$FD = $filterDefinitions->$fKey;
							if(isset($FD["whereSql"])) {
								$whereSql = $FD["whereSql"];
								$whereSql = preg_replace('/\?/', ":".$fKey, $whereSql);
								$whereParam = (isset($FD["whereParam"])?$FD["whereParam"]:'?');
								$whereParam = preg_replace('/\?/', $fVal, $whereParam);
								$qb->andWhere($whereSql);
								$qb->setParameter($fKey, $whereParam);
							}
						}
					}
					//echo "<hr /><pre>".$qb->getQuery()->getSQL().'<br />Parameters:: '.print_r($qb->getParameters(), true).'</pre>';
				}
			}
		}

		//LIMITS
		if ($applyLimits) {
			if (isset($filters->list)) {
				$limitstart = (isset($filters->list["limitstart"]) ? $filters->list["limitstart"] : 0);
				$limit = (isset($filters->list["limit"]) ? $filters->list["limit"] : 0);
				if ($limitstart) {
					$qb->setFirstResult($limitstart);
				}
				if ($limit) {
					$qb->setMaxResults($limit);
				}
			}
		}

		//ORDERING - todo: we need a way to be able to order on more than one column
		if ($applyOrdering) {
			if (isset($filters->list)) {
				$orderCol = (isset($filters->list["ordering"]) && !empty($filters->list["ordering"]) ? $filters->list["ordering"] : false);
				$orderDir = (isset($filters->list["direction"]) && !empty($filters->list["direction"]) ? $filters->list["direction"] : "ASC");
				if ($orderCol && $orderDir) {
					$qb->orderBy($orderCol, $orderDir);
				}
			}
		}
		return($qb);
	}

	/**
	 * Returns an array of entities identified by PK(===id)
	 * @param int|array $id
	 * @return array
	 */
	public function getEntitiesById($id) {
		$answer = [];
		if (is_array($id)) {
			$idArray = $id;
		} else {
			$idArray = [];
			$idArray[] = $id;
		}
		if (count($idArray)) {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			$qb = $em->createQueryBuilder();
			//Build Query
			$qb->select("i")->from($this->getEntityName(), "i");
			$qb->where('i.id IN (:idlist)');
			$qb->setParameter('idlist', $idArray);
			$query = $qb->getQuery();
			//echo '<hr /><pre>' .$query->getSQL().'<br />'.print_r($query->getParameters(), true).'</pre>';
			$answer = $query->getResult();
		}
		return($answer);
	}

	/**
	 * This method receives the posted data array (on form edit),
	 * loads the corresponding entity from db (if there is one)
	 * and returns an array with entity data -> overriden by data found in the $dataArray
	 *
	 * This serves the purpose of having a single interface to provide entity data for forms
	 * by quickly creating an array of values for any entity given the id,
	 * but at the same time when user inputs data on form (and entity validation fails)
	 * retaining that "bad" data so that it will still be available on the form for correction
	 *
	 * @param array $dataArray
	 * @return array
	 */
	public function getEditDataArray($dataArray) {
		$answer = [];
		if(isset($dataArray["id"]) && !empty($dataArray["id"])) {
			/** @var JEntityInterface $entity */
			$entity = $this->find($dataArray["id"]);
		} else {
			$entity = new $this->_entityName;
		}
		if(isset($entity)) {
			//echo "<pre>" . print_r($this->_class->getFieldNames(), true) . "</pre>";
			//echo "<pre>" . print_r($this->_class->getAssociationMappings(), true) . "</pre>";
			$fieldNames = $this->_class->getFieldNames();

			//we need associated fields so we can get an id - only if isOwningSide?? or many-to-1 ??? - todo: check on this
			$assocFields = [];
			foreach($this->_class->getAssociationMappings() as $k => $assFld) {
				if (isset($assFld["type"]) && $assFld["type"]==2) {//2 should be many-to-1
					$assocFields[] = $assFld["fieldName"];
				}
			}
			$answer = $entity->getPropsArray($fieldNames, $assocFields);
		}
		$answer = array_merge($answer, $dataArray);
		return($answer);
	}


	/**
	 * This method will receives the posted data array (on form save) and sets up the entity by
	 * 1) checking for id value and loading it from db / creating new one
	 * 2) mapping all additional data from posted data onto the entity
	 * @param array $dataArray
	 * @param bool $skipErrors - if true, mapDataArrayOnEntity will ignore exceptions thrown by setters methods - !DANGER AHEAD!
	 * @return JEntityInterface|null
	 */
	public function setupEntityByData($dataArray, $skipErrors=false) {
		if(isset($dataArray["id"]) && !empty($dataArray["id"])) {
			$answer = $this->find($dataArray["id"]);
		} else {
			$answer = new $this->_entityName;
		}
		if($answer && get_class($answer)==$this->_entityName) {
			$answer->mapDataArrayOnEntity($dataArray, $skipErrors);
		}
		return($answer);
	}


}