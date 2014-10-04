<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
use Doctrine\ORM\QueryBuilder;
defined('_JEXEC') or die();

/**
 * Defines how a repository(JRepository) should be declared
 *
 * Interface JRepositoryInterface
 */
interface JRepositoryInterface {

	/**
	 * The default method to get backend items filtered by defined filters
	 *
	 * $filters: {
	 *  "filters":{"search":"","type":"0", ...},
	 *  "list":{"ordering":"a.name","direction":"desc","limit":"25", ...}
	 * }
	 *
	 * @param \stdClass $filters
	 * @param \stdClass $filterDefinitions
	 * @return array
	 */
	public function getFilteredItems($filters, $filterDefinitions);


	/**
	 * This method, overriden in the specific Repo, should be adding
	 * SELECTs and JOINs on QB for listing filtered items
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListQuery($qb);


	/**
	 * The default method to count backend items filtered by defined filters
	 * @param \stdClass $filters
	 * @param \stdClass $filterDefinitions
	 * @return int
	 */
	public function countFilteredItems($filters, $filterDefinitions);


	/**
	 * This method, overriden in the specific Repo, should be adding
	 * SELECTs and JOINs on QB for counting filtered items
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListCountQuery($qb);


}