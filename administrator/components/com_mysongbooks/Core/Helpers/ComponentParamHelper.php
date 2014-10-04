<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die('Restricted access');
use MySongBooks\Core\Configuration;
use MySongBooks\Core\Exception\Exception;
/**
 * Reponsible for Component Parameters and Component Options:
 * Component Parameters - parameters set by the application like name, paths, url base, etc - memory based
 * Component Options - user modifiable options defined in MySongBooks\Core\Configuration - stored in db as extension's param
 *
 * Class ComponentParamHelper
 * @package MySongBooks\Core\Helpers
 */
class ComponentParamHelper {
	/** Holds the configurations options definitions as defined by MySongBooks\Core\Configuration
	 * @var array
	 */
	private static $configOptionsArray = null;
	/**
	 * Holds Component Parameters
	 * @var array
	 */
	private static $parameters = [];

	/**
	 * Transparent Setter method - if $name is defined in $configOptionsArray (it is a Component Option) it will be set and saved
	 * otherwise $name/$value pair will be treated as Component Parameter and stored in $parameters
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public static function setOption($name, $value) {
		$answer = true;//not much of an error check here ;)
		if(self::isConfigOption($name)) {
			//set param
			/** @var \Joomla\Registry\Registry $params */
			$params = \JComponentHelper::getParams(COMPONENT_ELEMENT_NAME_MYSONGBOOKS);
			$params->set($name, $value);
			//save params
			$db = \JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__extensions AS a');
			$query->set('a.params = ' . $db->quote((string)$params));
			$query->where('a.element = ' . $db->quote(COMPONENT_ELEMENT_NAME_MYSONGBOOKS));
			$db->setQuery($query);
			$db->execute();
		} else {
			self::$parameters[$name] = $value;
		}
		return($answer);
	}

	/**
	 * Transparent Getter function - if $name is defined in $configOptionsArray (it is a Component Option) it will be checked for and
	 * returned (or its default value from $configOptionsArray) otherwise it is a parameter
	 * @param string $name
	 * @return mixed
	 */
	public static function getOption($name) {
		$answer = false;
		if(self::isConfigOption($name)) {
			$answer = \JComponentHelper::getParams(COMPONENT_ELEMENT_NAME_MYSONGBOOKS)->get($name);
            if(empty($answer)) {
                $COA = self::getConfigOptionsArray();
                if(isset($COA[$name]["default"])) {
                    $answer = $COA[$name]["default"];
                }
            }
		} else {
			if(self::isParamOption($name)) {
				$answer = self::$parameters[$name];
			}
		}
		return($answer);
	}


	/**
	 * Checks if the given $name is a Configuration Option
	 * @param string $name
	 * @return bool
	 */
	public static function isConfigOption($name) {
		return(array_key_exists($name, self::getConfigOptionsArray()));
	}

	/**
	 * Checks if the given $name is a Configuration Option
	 * @param string $name
	 * @return bool
	 */
	public static function isParamOption($name) {
		return(array_key_exists($name, self::$parameters));
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return string
	 */
	public static function getParamValueNameFromList($name, $value) {
		$answer = "";
		if(self::isConfigOption($name)) {
			$COA = self::getConfigOptionsArray();
			if ($COA[$name]["type"] == "list" && isset($COA[$name]["options"]) && count($COA[$name]["options"])) {
				if(isset($COA[$name]["options"][$value])) {
					$answer = $COA[$name]["options"][$value];
				}
			}
		}
		return($answer);
	}

	/**
	 * @param string $group
	 * @return array
	 */
	public static function getParamsInGroup($group) {
		$answer = [];
		$COA = self::getConfigOptionsArray();
		foreach($COA as $key => $param) {
			if($param["group"] == $group) {
				$answer[$key] = $param;
			}
		}
		return($answer);
	}

	/**
	 * @return array
	 */
	public static function getParamGroups() {
		$answer = [];
		$COA = self::getConfigOptionsArray();
		foreach($COA as &$param) {
			if(!in_array($param["group"], $answer)) {
				array_push($answer, $param["group"]);
			}
		}
		return($answer);
	}



	/**
	 * called by installer script in install/script.php
	 */
	public static function recheckConfigurationAndSetDefaultConfiguration() {
		$COA = self::getConfigOptionsArray();
		foreach($COA as $key => $param) {
			self::_checkDefaultConfigurationFor($key, $param);
		}
	}

	/**
	 * @param $name
	 * @param $param
	 */
	private static function _checkDefaultConfigurationFor($name, $param) {
		$value = self::getOption($name);
		if(!$value) {
			//special cases
			switch($name) {
				default:
					break;
			}
			if(!$value) {
				$value = $param["default"];
			}
			self::setOption($name, $value);
		}
	}


	/**
	 * @return array
	 */
	public static function getConfigOptionsArray() {
		if(self::$configOptionsArray === null) {
			$Cfg = new Configuration();
			self::$configOptionsArray = $Cfg->getConfigurationOptions();
		}
		return(self::$configOptionsArray);
	}


	/**
	 * types: text, textarea, number, [path], list, userlist
	 * @param string $paramName
	 * @param mixed $paramValue
	 * @throws Exception
	 * @return bool
	 */
	public static function submitParamEditForm($paramName, $paramValue) {
		$answer = "";
		if(!empty($paramName)) {
			if (self::isConfigOption($paramName)) {
				$COA = self::getConfigOptionsArray();
				$param = $COA[$paramName];
				if(!isset($param["readonly"]) || !$param["readonly"]) {
					if(!$param["required"] || ($paramValue!=""&&$paramValue!=null)) {
						switch($param["type"]) {
							case "text":
							case "textarea":
								if(!isset($param["validation"]) || (!empty($param["validation"]) && preg_match($param["validation"], $paramValue))) {
									$answer = self::setOption($paramName, $paramValue);
								} else {
									$answer = "Validation failed! You need to match this: '" . $param["validation"] . "'.";
								}
								break;
							case "number";
								$paramValue = (int)$paramValue;
								if(isset($param["min"]) && $paramValue < $param["min"]) {
									$answer = "Validation failed! Your value must be higher than or equal to: " . $param["min"] . ".";
									break;
								}
								if(isset($param["max"]) && $paramValue > $param["max"]) {
									$answer = "Validation failed! Your value must be lower than or equal to: " . $param["max"] . ".";
									break;
								}
								$answer = self::setOption($paramName, $paramValue);
								break;
							case "list":
								$answer = self::setOption($paramName, $paramValue);
								break;
							default:
								$answer = "Parameter type error! The type of parameter you passed is unknown: '" . $param["type"] . "'.";
								break;
						}
					} else {
						$answer = "Required parameter! You need to enter a value here.";
					}
				} else {
					$answer = "Read-only parameter! You cannot change this one.";
				}
				if($answer !== true) {
					throw new Exception("Error - $answer", 500);
				}
			} else {
				throw new Exception("Error - Unknown parameter name ( $paramName )!", 500);
			}
		} else {
			throw new Exception("Error - No parameter name supplied!", 500);
		}
		return($answer);
	}

	/**
	 * @param string $paramName
	 * @throws Exception
	 * @return string
	 */
	public static function getParamEditForm($paramName) {
		$answer = '';
		if(!empty($paramName)) {
			if (self::isConfigOption($paramName)) {
				$COA = self::getConfigOptionsArray();
				$param = $COA[$paramName];
				//label
				$answer .= '<label for="paramValue">'.$param["label"].'</label>';
				$val = self::getOption($paramName);
				$width = (isset($param["width"])?$param["width"]:500);
				$height = (isset($param["height"])?$param["height"]:100);
				$min = (isset($param["min"])?$param["min"]:false);
				$max = (isset($param["max"])?$param["max"]:false);

				if(!isset($param["readonly"]) || !$param["readonly"]) {
					switch($param["type"]) {
						case "text":
							$answer .= '<input type="text" style="width:'.$width.'px;" value="'.$val.'" name="paramValue" id="paramValue" />';
							break;
						case "textarea":
							$answer .= '<textarea style="width:'.$width.'px;height:'.$height.'px;" name="paramValue" id="paramValue">'.$val.'</textarea>';
							break;
						case "number":
							$answer .= '<input type="number" style="width:'.$width.'px;" value="'.$val.'"'.($min!==false?' min="'.$min.'"':'').($max!==false?' max="'.$max.'"':'').' name="paramValue" id="paramValue" />';
							break;
						case "list":
							$answer .= '<select style="width:'.$width.'px;" name="paramValue" id="paramValue">';
							foreach($param["options"] as $k=>$v) {
								$selected = ($k==$val?' selected="selected"':'');
								$answer .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
							}
							$answer .= '</select>';
							break;
						case "userlist":
							//note $val will contain a json encoded array : ["o1","o2","o3"] so in input we must use single quotes
							$answer .= '<input type="text" value=\''.$val.'\' name="paramValue" id="paramValue" />';
							break;
						default:
							$answer .= 'Unhandled parameter type['.$param["type"].']!';
					}
				} else {
					$answer .= '<p>READ ONLY: ' . $val . '</p>';
				}
				//description
				$answer .= '<p class="description">'
					.$param["description"]
					.'<br />'
					.(isset($param["required"])&&$param["required"]?"Required":"")
					.($min!==false?"&nbsp;Min:".$param["min"]:"")
					.($max!==false?"&nbsp;Max:".$param["max"]:"")
					.'</label>';
			} else {
				throw new Exception("Error - Unknown parameter name ( $paramName )!", 500);
			}
		} else {
			throw new Exception("Error - No parameter name supplied!", 500);
		}
		return($answer);
	}


}