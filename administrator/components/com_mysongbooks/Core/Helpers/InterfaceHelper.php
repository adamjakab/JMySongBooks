<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Helpers;
defined('_JEXEC') or die();
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Joomla\JEntityInterface;
//
use MySongBooks\Core\Repository\DummyRepository;
use MySongBooks\Core\Entity\Dummy;

/**
 * Class InterfaceHelper
 */
class InterfaceHelper {
	/**
	* Adds buttons to toolbar
	*
	* @param	array	    array of arrays with the following values in this order:
	* @string	$acl		The Acl action to control
	* @string	$task		The task to perform in the form of controllerName.controllerMethod
	* @string	$icon		The image to display
	* @string	$title		The title of the button
	* @bool	    $listSelect	True if it is required to check that at least one list item is checked
	*/
	public static function addButtonsToToolBar($buttons=[]) {
	    if ($buttons&& is_array($buttons)&&count($buttons)) {
	        $canDo = self::getActions();
	        foreach($buttons as &$button) {
			    if ($canDo->get($button[0])) {
                    \JToolBarHelper::custom($button[1], $button[2], $button[2], $button[3], (isset($button[4])&&$button[4]===true));
			    }
	        }
	    }
	}

	/**
	 * Configure and return the Side Menu bar
	 * @param string $submenu
	 * @return string
	 */
	public static function getSidemenu($submenu="cp") {
		$linkBase = 'index.php?option=' . CPH::getOption("com_name");
		\JHtmlSidebar::addEntry(\JText::_('MYSONGBOOKS_SIDEBAR_CP'),$linkBase,($submenu=='cp'));
		\JHtmlSidebar::addEntry(\JText::_('MYSONGBOOKS_SIDEBAR_CHORDS'),$linkBase . '&task=chords.display',($submenu=='chords'));
        \JHtmlSidebar::addEntry(\JText::_('MYSONGBOOKS_SIDEBAR_CONFIGURATIONS'),$linkBase . '&task=configurations.display',($submenu=='configurations'));
		return(\JHtmlSidebar::render());
	}


	/**
	 * Sets Joomla Header title and page title
	 * @param $ctrl
	 */
	public static function setHeaderTitle($ctrl) {
        $ctrl=($ctrl?strtoupper($ctrl):"UNKNOWN");
	    \JToolBarHelper::title(COMPONENT_INSTANCE_NAME_MYSONGBOOKS . " - " . \JText::_("MYSONGBOOKS_SIDEBAR_".$ctrl), 'nope');
	}

    /**
     * @param int $messageId
     * @return \JObject
     */
    public static function getActions($messageId = 0) {
        $result	= new \JObject;
        $user	= \JFactory::getUser();
        if (empty($messageId)) {
            $assetName = CPH::getOption("com_name");
        } else {
            $assetName = CPH::getOption("com_name") . '.message.'.(int) $messageId;
        }
        $actions = [
            'core.admin',
            'core.manage'
        ];
        foreach ($actions as $action) {
            $result->set($action,	$user->authorise($action, $assetName));
        }
        return $result;
    }

	/*-----------------------------------------------------------------------------------------------------VIEW*/
	/**
	 * Returns the rendered filter bar fr the current list view
	 * @param \stdClass $filters
	 * @param array $options
	 * @return string
	 */
	public static function getFilterBarForView($filters, $options) {
		$filtersBasePath = CPH::getOption("com_path_admin") . DS . 'layouts';
		$answer = \JLayoutHelper::render('filterbar', $filters, $filtersBasePath, $options);
		return($answer);
	}

	/*-----------------------------------------------------------------------------------------------------HTML(generic)*/

    /**
     * @param string $id
     * @param string $label
     * @param mixed $value
     * @param array $options
     * @param string $type
     * @return string
     * @throws Exception
     */
    public static function getInputField($id, $label=null, $value=null, $options=[], $type="text") {
        if(!in_array($type,["text","textarea","select","radio","hidden"])){throw new Exception("Unknown field type($type)!");}
        $id = preg_replace('#\W#', '', $id);
        $formGroupName = 'jform';
        $dataGroupName = (isset($options["datagroup"])?$options["datagroup"]:false);
        $fieldId = $formGroupName . ($dataGroupName?'_'.$dataGroupName:'') . '_' . $id;
        $fieldName = $formGroupName . ($dataGroupName?'['.$dataGroupName.']':'') . '['.$id.']';
        $labelId = $fieldId.'-lbl';
        $fieldClass = (isset($options["class"])?' '.$options["class"]:'');
	    $fieldStyle = (isset($options["style"])?' style="'.$options["style"].'"':'');
        $required = (isset($options["required"])&&$options["required"]);
        $tooltip = (isset($options["tooltip"])?$options["tooltip"]:false);

        //start field ctrlGrp
        $ctrlGrpHtml = '%s%s';
        if($type!="hidden") {
            $ctrlGrpHtml = '<div class="control-group">%s%s</div>'.NL;
        }

        /*THE LABEL*/
        $labelHtml = '';
        if($type!="hidden" && $label) {
            $labelHtml = '<div class="control-label">'.NL
                .'<label id="'.$labelId.'" for="'.$fieldId.'" class="'.($tooltip?'hasTooltip ':'').($required?'required ':'').'"'.($tooltip?' data-original-title="'.$tooltip.'"':'').'>'.NL
                .$label . ($required?'<span class="star">&nbsp;*</span>':'')
                .'</label>'.NL
                .'</div>'.NL;
        }


        /*THE FIELD*/
        $fieldHtml = '<div class="controls">%s</div>'.NL;
        switch($type) {
            case "text":
                $fieldHtml = sprintf($fieldHtml, '<input type="text" name="'.$fieldName.'" id="'.$fieldId.'" value="'.$value.'" class="'.$fieldClass.'"'.($required?'required="" ':'').$fieldStyle.' />');
                break;
	        case "textarea":
				$rows = (isset($options["rows"])?' rows="'.$options["rows"].'"':'');
		        $cols = (isset($options["cols"])?' cols="'.$options["cols"].'"':'');
		        $fieldHtml = sprintf($fieldHtml, '<textarea name="'.$fieldName.'" id="'.$fieldId.'" class="'.$fieldClass.'"'.($required?'required="" ':'').$rows.$cols.$fieldStyle.'>'.$value.'</textarea>');
		        break;
            case "select":
                $selectOptions = (isset($options["options"])?$options["options"]:[]);
                $selectField = \JHTML::_('select.genericlist', $selectOptions, $fieldName, 'size="1" class="'.$fieldClass.'"'.$fieldStyle,'value', 'text', $value, $fieldId );
                $fieldHtml = sprintf($fieldHtml, $selectField);
                break;
            case "radio":
                //we cannot use \JHTML::_('select.radiolist'... because it puts inputs inside labels
                //so we must render this here
                //additional class: btn-group-yesno
                $radioOptions = (isset($options["options"])?$options["options"]:[]);
                //
                $radioField = '<fieldset id="'.$fieldId.'" class="radio btn-group'.$fieldClass.'">%s</fieldset>';
                $fieldHtml = sprintf($fieldHtml, $radioField);
                //
                $radioField = '';
                foreach ($radioOptions as $opt) {
                    $opt_val = (isset($opt->value)?$opt->value:'-undefined-');
                    $opt_txt = (isset($opt->text)?$opt->text:'-undefined-');
                    $opt_id = $fieldId.$opt_val;
                    $opt_chk = ($opt_val==$value?' checked="checked"':'');
                    $radioField .= '<input type="radio" id="'.$opt_id.'" name="'.$fieldName.'" value="'.$opt_val.'"'.$opt_chk.' />';
                    $radioField .= '<label for="'.$opt_id.'">'.$opt_txt.'</label>';
                }
                $fieldHtml = sprintf($fieldHtml, $radioField);
                break;
            case "hidden":
                $fieldHtml = '<input type="hidden" name="'.$fieldName.'" id="'.$fieldId.'" value="'.$value.'" />'.NL;
                break;
            default:
                break;
        }

        //assemle and return
        $answer = sprintf($ctrlGrpHtml, $labelHtml, $fieldHtml);
        return($answer);
    }


    /** To be used with arrays with no keys where value===text
     * @param array $data
     * @param bool|string $zeroOption
     * @return array
     */
    public static function getSelectOptionsFromArray($data, $zeroOption=false) {
        $lst = [];
        if ($zeroOption!==false) {
            $lst[] = \JHTML::_('select.option',  '', \JText::_($zeroOption), 'value', 'text' );
        }
        if(is_array($data)&&count($data)) {
            foreach($data as $d) {
                $lst[] = \JHTML::_('select.option',  $d, $d, 'value', 'text' );
            }
        }
        return ($lst);
    }

	/**
	 * GENERIC YES/NO OPTIONS
	 * @param bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_YesNo($zeroOption=false) {
		$lst = [];
		if($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '-', \JText::_($zeroOption), 'value', 'text' );
		}
		$lst[] = \JHTML::_('select.option',  '0', \JText::_("MYSONGBOOKS_NO"), 'value', 'text' );
		$lst[] = \JHTML::_('select.option',  '1', \JText::_("MYSONGBOOKS_YES"), 'value', 'text' );
		return ($lst);
	}

	/**
	 * Published state options
	 * @param bool|string $zeroOption
	 * @return array
	 */
	public static function getSelectOptionsPublishedStates($zeroOption=false) {
        $lst = [];
		if($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '', \JText::_($zeroOption), 'value', 'text' );
		}
        $lst[] = \JHTML::_('select.option',  '1', \JText::_("MYSONGBOOKS_PUBLISHED"), 'value', 'text' );
        $lst[] = \JHTML::_('select.option',  '0', \JText::_("MYSONGBOOKS_UNPUBLISHED"), 'value', 'text' );
        $lst[] = \JHTML::_('select.option',  '2', \JText::_("MYSONGBOOKS_ARCHIVED"), 'value', 'text' );
        $lst[] = \JHTML::_('select.option',  '-2', \JText::_("MYSONGBOOKS_TRASHED"), 'value', 'text' );
        return ($lst);
    }


	/**
	 * The method name says it all ;) - returns rendered state controller field(published/unpublished/etc) for in item in listing view
	 * @param string $ctrl - the name of the current controller
	 * @param int $index - the index number in the listing (the same one the checkbox element is using for this item)
	 * @param JEntityInterface $entity - the entity
	 * @return string $answer
	 */
	public static function getEntityListingStateControlBox($ctrl, $index, $entity) {
		$answer = '';
		if ($ctrl && $entity) {
			$answer .= '<div class="btn-group">';//this is necessary for correct rendering
			//create the state control box
			$answer .= \JHtml::_('jgrid.published', $entity->getPublished(), $index, $ctrl.'.', true, 'cb');
			//add dropdown options
			if ($entity->getPublished()==0 || $entity->getPublished()==1) {
				\JHtml::_('actionsdropdown.archive', 'cb' . $index, $ctrl);
				\JHtml::_('actionsdropdown.trash', 'cb' . $index, $ctrl);
			} else if ($entity->getPublished()==2) {//archived
				\JHtml::_('actionsdropdown.unarchive', 'cb' . $index, $ctrl);
			} else if ($entity->getPublished()==-2) {
				\JHtml::_('actionsdropdown.untrash', 'cb' . $index, $ctrl);
			}
			$answer .= \JHtml::_('actionsdropdown.render');
			$answer .= '</div>';
		}
		return($answer);
	}





    /*-------------------------------------------------------------------------------------------------------*/
    public static function setDefaultHeaderIncludes() {
	    $comName = CPH::getOption("com_name");
	    \JHtml::_('jquery.framework');
		if(CPH::getOption('com_location') == "frontend") {
			self::addAdditionalHeaderIncludes("css", "/media/${comName}/css/front.css");
			self::addAdditionalHeaderIncludes("js", "/media/${comName}/js/front.js");
		} else {
			self::addAdditionalHeaderIncludes("css", "/media/${comName}/css/back.css");
			self::addAdditionalHeaderIncludes("js", "/media/${comName}/js/back.js");
		}
	}

	/**
	 * @param string $type (css|js)
	 * @param string $path - path is intended from site root
	 */
	public static function addAdditionalHeaderIncludes($type, $path) {
		/** @var $document \JDocumentHTML */
		$document = \JFactory::getDocument();
		if($type == "css") {
			$document->addStyleSheet($path);
		} else if ($type == "js") {
			/*
			$head = $document->getHeadData();
			$jsType = ['mime'=>'text/javascript', 'defer'=>false, 'async'=>false];
			$head['scripts'] = array_merge($head['scripts'], [$path => $jsType]);
			$document->setHeadData($head);
			*/
			$document->addScript($path);
		}
	}

	/**
	 * Returns component's xml parsed into an array
	 * component's xml can be named both: com_comname.xml or comname.xml so we test for both
	 * @return array
	 */
	public static function getInstallXmlData() {
		$answer = [];
		$xmlFileName = COMPONENT_ELEMENT_NAME_MYSONGBOOKS . ".xml";
		if(!file_exists(CPH::getOption("com_path_admin")."/".$xmlFileName)) {
			$xmlFileName = str_replace("com_","", $xmlFileName);
			if(!file_exists(CPH::getOption("com_path_admin")."/".$xmlFileName)) {
				$xmlFileName = false;
			}
		}
		if($xmlFileName) {
			$xml = simplexml_load_file(CPH::getOption("com_path_admin")."/".$xmlFileName);
			if ($xml->getName() == 'extension') {
				$answer['name'] = (string) $xml->name;
				$answer['creationDate'] = (string) $xml->creationDate;
				$answer['author'] = (string) $xml->author;
				$answer['authorEmail'] = (string) $xml->authorEmail;
				$answer['authorUrl'] = (string) $xml->authorUrl;
				$answer['version'] = (string) $xml->version;
				$answer['description'] = (string) $xml->description;
				$answer['copyright'] = (string) $xml->copyright;
				$answer['license'] = (string) $xml->license;
			}
		}
		return ($answer);
	}

}
