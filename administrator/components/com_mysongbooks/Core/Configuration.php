<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core;
defined('_JEXEC') or die();

/**
 * Defines Component parameters editable by user
 *
 * Class Configuration
 */
class Configuration {
    /** @var  array */
    private $configurationOptions;

    /**
     * todo: add a path selector type=path
     *
     * Defines component configuration options
     *
     * KEY          TYPE        VALUES                                      DEFAULT
     * ------------------------------------------------------------------------------------------
     * group        string      any                                         null
     * type         string      (text|textarea|number|list|userlist)        null
     * options      array       any - applies to type:list                  null
     * min          int         any - applies to type:number                null
     * max          int         any - applies to type:number                null
     * validation   string      regular expression                          null
     * default      mixed       any                                         null
     * required     boolean     (true|false)                                false
     * readonly     boolean     (true|false)                                false
     * width        int         any - applies to all types                  500
     * height       int         any - applies to type:textarea              100
     *
     * Types:
     * text             standard <input type="text"/> field
     * textarea         standard <textarea/> field
     * number           standard <input type="number"/> field with stepper between min/max - validation unnecessary(will accept intergers only)
     * list             standard <select/> field with predefined options
     * userlist         custom list creator which stores array of user defined options
     *                  (predefined options still available and must be a json string in the form of ["opt1","opt2",...])
     *
     */
    public function __construct() {
        //--------------------------------------------------------------------GENERAL
	    $this->configurationOptions["default_note_naming"] = [
		    "group"         => "general",
		    "type"          => "list",
		    "options"       => ["C"=>\JText::_("MYSONGBOOKS_NOTENAMING_C"),"Do"=>\JText::_("MYSONGBOOKS_NOTENAMING_DO")],
		    "default"       => "C",
		    "required"      => true,
		    "width"         => 180
	    ];
	    /*
        $this->configurationOptions["dummy_types"] = [
            "group"         => "general",
            "type"          => "text",
            "validation"    => "#^[a-z0-9]+(,[a-z0-9]+)*$#i",
            "default"       => "type-1, type-2, type-3",
            "required"      => false
        ];

	    $this->configurationOptions["list_maker"] = [
		    "group"         => "general",
		    "type"          => "userlist",
		    "default"       => json_encode(["opt1", "opt2", "opt3"]),
		    "required"      => false
	    ];

	    $this->configurationOptions["counter"] = [
		    "group"         => "general",
		    "type"          => "number",
		    "default"       => 5,
		    "min"           => 0,
		    "max"           => 10,
		    "required"      => true,
		    "width"         => 80
	    ];*/

        //--------------------------------------------------------------------ADVANCED
        $this->configurationOptions["show_button_sync_db"] = [
            "group"         => "advanced",
            "type"          => "list",
            "options"       => ["Y"=>\JText::_("MYSONGBOOKS_YES"),"N"=>\JText::_("MYSONGBOOKS_NO")],
            "default"       => "N",
            "required"      => true,
            "width"         => 80
        ];

        //auto-setting translated labels & descriptions
        $STRBASE = str_replace("com_", "", COMPONENT_ELEMENT_NAME_MYSONGBOOKS) . "_CFG_PARAM_";
        foreach($this->configurationOptions as $k => &$a) {
            if(!isset($a["label"])) {
                $a["label"] = \JText::_(strtoupper($STRBASE."NAME_".$k));
            }
            if(!isset($a["description"])) {
                $a["description"] = \JText::_(strtoupper($STRBASE."DESC_".$k));
            }
        }
    }

	/**
	 * @return array
	 */
	public function getConfigurationOptions() {
        return($this->configurationOptions);
    }
}