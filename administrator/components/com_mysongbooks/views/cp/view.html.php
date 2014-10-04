<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
use MySongBooks\Core\Joomla\JView;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Exception\Exception;

/**
 * ControlPanel View
 */
class MySongBooksViewCp extends JView {
    public $sidebar;

	/**
	 * @param array $config
	 */
	public function __construct($config=[]) {
		parent::__construct($config);
    }

	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws MySongBooks\Core\Exception\Exception
	 */
	public function display($tpl=null) {
		//set buttons
		IFH::addButtonsToToolBar([]);
		//display the view
		parent::display($tpl);
	}


	/**
	 * @return array
	 */
	protected function getCpInfo() {
		$xmlData = IFH::getInstallXmlData();
		$answer = [
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_COMPNAME'),"value"=>$xmlData["name"]],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_COMPDESC'),"value"=>\JText::_($xmlData["description"])],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_VERSION'),"value"=>$xmlData["version"]],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_RELDATE'),"value"=>$xmlData["creationDate"]],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_AUTHOR'),"value"=>'<a href="mailto:'.$xmlData["authorEmail"].'">'.$xmlData["author"].'</a>'],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_SUPPORT'),"value"=>'<a href="'.$xmlData["authorUrl"].'" target="_blank">' . $xmlData["authorUrl"] . '</a>'],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_LICENSE'),"value"=>$xmlData["license"]],
			["key"=>\JText::_('MYSONGBOOKS_CP_INFO_COPYRIGHT'),"value"=>$xmlData["copyright"]]
		];
		return($answer);
	}
}
