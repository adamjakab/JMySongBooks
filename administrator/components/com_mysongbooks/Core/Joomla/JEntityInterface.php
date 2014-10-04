<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
defined('_JEXEC') or die();

/**
 * Defines how an entity(JEntity) should be declared
 *
 * Interface JEntityInterface
 */
interface JEntityInterface {

	/**
	 * Constructor
	 */
	public function __construct();

	/**
	 * @return integer
	 */
	public function getId();

	/**
	 * @return integer
	 */
	public function getPublished();

	/**
	 * @param integer $published
	 */
	public function setPublished($published);

	/**
	 * Implemented on MySongBooks\Core\Joomla\JEntity
	 * @param array $classProperties
	 * @param array $associationProperties
	 * @return array
	 */
	public function getPropsArray($classProperties, $associationProperties);

}