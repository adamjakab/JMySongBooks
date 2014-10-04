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
use MySongBooks\Core\Repository\ChordLayoutRepository;
use \MySongBooks\Core\Entity\ChordLayout;

/**
 * To load this view use layout=json
 * Class MySongBooksViewJsonChords
 */
class MySongBooksViewJsonChords extends JView {
    /**
     * @param array $config
     */
    function __construct($config=[]) {
        parent::__construct($config);
	    header('Content-type: application/json; charset=utf-8');
    }


	/**
	 * Stores the chord layout scheme
	 */
	public function saveLayoutScheme() {
		$answer = new stdClass();
		$JI = \JFactory::getApplication()->input;
		$formData = [];
		$formData["id"] = $JI->getInt("id", null);
		$formData["scheme"] = $JI->getString("scheme", null);
		try {
			/** @var \Doctrine\ORM\EntityManager $em */
			$em = CPH::getOption("EntityManager");
			/** @var ChordLayoutRepository $repo */
			$repo = $em->getRepository('MySongBooks\Core\Entity\ChordLayout');
			/** @var ChordLayout $entity */
			$entity = $repo->setupEntityByData($formData);
			$em->persist($entity);
			$em->flush();
			$answer->result = "OK";
		} catch (Exception $e) {
			$answer->errors = $e->getErrorObject();
		} catch (\Exception $e) {
			$e = new Exception($e->getMessage(), $e->getCode());
			$answer->errors = $e->getErrorObject();
		}
		echo json_encode($answer);
		\JFactory::getApplication()->close();
	}
}