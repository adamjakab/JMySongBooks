<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */


/**
 * Autoloader for Application Core classes
 */
spl_autoload_register(function ($class) {
    /** @var string $CCBN - Component Class Base Name */
    $CCBN = COMPONENT_INSTANCE_NAME_MYSONGBOOKS;
    if (preg_match('#^'.$CCBN.'\\\#', $class)) {
        if(!class_exists($class)) {
            $realpath = realpath(dirname(dirname(__DIR__)) . DS . str_replace('\\', '/', str_replace($CCBN.'\\','', $class)) . '.php');
            if ($realpath) {
                require_once($realpath);
            }
        }
    }
}, true, true);
