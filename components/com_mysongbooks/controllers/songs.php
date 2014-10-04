<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */
defined('_JEXEC') or die;
use MySongBooks\Core\Joomla\JController;

/**
 * Class MySongBooksControllerSongs
 */
class MySongBooksControllerSongs extends JController {
	/**
	 * @param array $config
	 */
	function __construct($config=[]) {
		parent::__construct($config);
	}
}
