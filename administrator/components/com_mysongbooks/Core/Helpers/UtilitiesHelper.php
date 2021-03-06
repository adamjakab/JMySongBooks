<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die();

/**
 * Generic utility methods
 *
 * Class UtilitiesHelper
 */
class UtilitiesHelper {

	/**
	 * Translates a camel case string into a string with
	 * underscores (e.g. firstName -> first_name)
	 * @author http://paulferrett.com/
	 *
	 * @param string $str String in camel case format
	 * @return string $str Translated into underscore format
	 */
	public static function from_camel_case($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}

	/**
	 * Translates a string with underscores
	 * into camel case (e.g. first_name -> firstName)
	 * @author http://paulferrett.com/
	 *
	 * @param string $str String in underscore format
	 * @param bool $capitalise_first_char If true, capitalise the first char in $str
	 * @return string $str translated into camel caps
	 */
	public static function to_camel_case($str, $capitalise_first_char = false) {
		if($capitalise_first_char) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

}