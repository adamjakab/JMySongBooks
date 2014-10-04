<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */


//component name defines
try {
    if(!defined("COMPONENT_ELEMENT_NAME_MYSONGBOOKS")) {
        define("COMPONENT_ELEMENT_NAME_MYSONGBOOKS", "com_mysongbooks");
    } else {
        throw new Exception("The constant COMPONENT_ELEMENT_NAME_MYSONGBOOKS is already defined!");
    }
    if(!defined("COMPONENT_INSTANCE_NAME_MYSONGBOOKS")) {
        define("COMPONENT_INSTANCE_NAME_MYSONGBOOKS", "MySongBooks");
    } else {
        throw new Exception("The constant COMPONENT_INSTANCE_NAME_MYSONGBOOKS is already defined!");
    }

} catch(\Exception $e) {
    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
    return;
}


//------------------------------------------OTHER BASIC DEFINITIONS--------------------------------//

/**
 * A newline character for cleaner HTML styling.
 */
defined('BR') || define('BR', '<br />');

/**
 * A newline character for cleaner <pre> styling.
 */
defined('NL') || define('NL', "\n");

/**
 * Combined BR+NL.
 */
defined('BRNL') || define('BRNL', BR.NL);

