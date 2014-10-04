<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Repository;
defined('_JEXEC') or die();
use MySongBooks\Core\Joomla\JRepository;
use MySongBooks\Core\Joomla\JRepositoryInterface;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use Doctrine\ORM\QueryBuilder;



/**
 * Class ChordTypeRepository
 */
class ChordTypeRepository extends JRepository implements JRepositoryInterface {

	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListQuery($qb) {
		$qb->select("i")->from('MySongBooks\Core\Entity\ChordType', "i");
		return($qb);
	}


	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListCountQuery($qb) {
		$qb->select("count(i)")->from('MySongBooks\Core\Entity\ChordType', "i");
		return($qb);
	}


	/**
	 * Returns simple array of id/name pairs - normally for select dropdown options
	 * @return array
	 */
	public function getSelectList() {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$qb = $em->createQueryBuilder();
		$qb->select(["i.id AS id", "CONCAT(i.name,'(',i.abbreviation,')') AS name"])->from('MySongBooks\Core\Entity\ChordType', "i");
		$qb->orderBy("i.id", "asc");
		$query = $qb->getQuery();
		$answer = $query->getArrayResult();
		return($answer);
	}




}
