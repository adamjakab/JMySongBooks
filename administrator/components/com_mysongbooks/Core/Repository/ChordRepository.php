<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Repository;
defined('_JEXEC') or die();
use MySongBooks\Core\Entity\Chord;
use MySongBooks\Core\Joomla\JRepository;
use MySongBooks\Core\Joomla\JRepositoryInterface;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;


/**
 * Class ChordRepository
 */
class ChordRepository extends JRepository implements JRepositoryInterface {

	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListQuery($qb) {
		$qb->select("i","k")->from('MySongBooks\Core\Entity\Chord', "i");
		$qb->innerJoin('i.type', 'j');
		$qb->leftJoin('i.chord_layouts',"k");
		//$qb->groupBy("i.id");
		return($qb);
	}


	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListCountQuery($qb) {
		$qb->select("count(i)")->from('MySongBooks\Core\Entity\Chord', "i");
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
		$qb->select(["i.id AS id", "i.title AS name"])->from('MySongBooks\Core\Entity\Chord', "i");
		$qb->orderBy("i.title", "asc");
		$query = $qb->getQuery();
		$answer = $query->getArrayResult();
		return($answer);
	}


	/**
	 * Finds the ONLY chord that matches parameters - if more than one(should not happen) returns null
	 * @param int $rootNote
	 * @param string $abbrStr
	 * @return Chord|null
	 */
	public function getChordByRootNoteAndAbbrString($rootNote, $abbrStr) {
		/** @var \Doctrine\ORM\EntityManager $em */
		$em = CPH::getOption("EntityManager");
		$qb = $em->createQueryBuilder();
		$qb->select("i")->from('MySongBooks\Core\Entity\Chord', "i")
			->innerJoin('i.type', 'j', 'k')
			->leftJoin('i.chord_layouts',"k")
			->where('i.root_note = :root_note')->setParameter('root_note',$rootNote);
		//
		if(!empty($abbrStr)) {
			$qb->andWhere($qb->expr()->orX(
				$qb->expr()->eq('j.abbreviation', ':abbreviation'),
				$qb->expr()->like('j.alt_abbreviations', ':abbreviationLIKE')
			))->setParameter('abbreviation',$abbrStr)->setParameter('abbreviationLIKE','%"'.$abbrStr.'"%');
		} else {
			$qb->andWhere('j.abbreviation = :abbreviation')->setParameter('abbreviation',$abbrStr);
		}
		//
		$query = $qb->getQuery();
		//echo '<pre>'.$query->getSQL().' - PARAMS: '.print_r($query->getParameters()->toArray(), true).'</pre>';

		try {
			$answer = $query->getOneOrNullResult();
		} catch (NonUniqueResultException $e) {
			$answer = null;
		}
		return($answer);
	}


}
