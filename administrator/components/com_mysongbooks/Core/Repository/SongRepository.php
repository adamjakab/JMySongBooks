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
 * Class SongRepository
 */
class SongRepository extends JRepository implements JRepositoryInterface {

	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListQuery($qb) {
		$qb->select("i")->from('MySongBooks\Core\Entity\Song', "i");
		return($qb);
	}


	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	public function setupListCountQuery($qb) {
		$qb->select("count(i)")->from('MySongBooks\Core\Entity\Song', "i");
		return($qb);
	}


}