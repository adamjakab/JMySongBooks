<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
defined('_JEXEC') or die();
use MySongBooks\Core\Exception\ValidationException;
use MySongBooks\Core\Helpers\UtilitiesHelper;

/**
 *
 * Class JEntity
 */
class JEntity {
	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Maps associative data array to entity by finding appropriate setter methods
	 * @param array $dataArray
	 * @param bool $skipErrors - if true it will ignore exception thrown by setter method
	 * @throws ValidationException
	 */
	public function mapDataArrayOnEntity($dataArray, $skipErrors=false) {
		foreach($dataArray as $k => $v) {
			//$methodName = "set".ucfirst(strtolower($k));
			$methodName = UtilitiesHelper::to_camel_case("set_".strtolower($k));
			if (method_exists($this, $methodName)) {
				try {
					call_user_func_array([$this, $methodName], [$v]);
				} catch (ValidationException $e) {
					if(!$skipErrors) {
						throw $e;
					}
				}
			} else {
				//echo "No method by this name: " . $methodName;
			}
		}
	}

	/**
	 * Returns associative array of Entity property/value pairs required by $classProperties array
	 * @param array $classProperties - list of column names
	 * @param array $associationProperties - list of column names where other entity is associated - we need id
	 * @return array
	 */
	public function getPropsArray($classProperties, $associationProperties) {
		$answer = [];
		if (count($classProperties)) {
			foreach ($classProperties as $property) {
				$methodName = UtilitiesHelper::to_camel_case("get_".strtolower($property));
				if (method_exists($this, $methodName)) {
					$propValue = call_user_func([$this, $methodName]);
					$answer[$property] = $propValue;
				}
			}
		}
		if (count($associationProperties)) {
			foreach ($associationProperties as $property) {
				$methodName = UtilitiesHelper::to_camel_case("get_".strtolower($property));
				if (method_exists($this, $methodName)) {
					/** @var JEntityInterface $entity */
					$entity = call_user_func([$this, $methodName]);
					if($entity) {
						$answer[$property] = $entity->getId();
					}
				}
			}
		}
		return($answer);
	}


}