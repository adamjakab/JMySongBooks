<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
use MySongBooks\Core\Joomla\JView;
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper;
use MySongBooks\Core\Helpers\DatabaseUpdaterHelper;

/**
 * Class MySongBooksViewConfigurations
 */
class MySongBooksViewConfigurations extends JView {
	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws MySongBooks\Core\Exception\Exception
	 */
	public function display($tpl=null) {
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode('<br />', $errors), 500);
		}

		//set buttons
        InterfaceHelper::addButtonsToToolBar([
            (CPH::getOption("show_button_sync_db") == "Y"
                ?["core.manage", "configurations.checkAndUpdateTables", 'database', JText::_('MYSONGBOOKS_CFG_BTN_SYNCDB')]
                :null
            )
		]);

		//PREFERENCES BUTTON(com_config popup)
		$canDo = InterfaceHelper::getActions();
		if ($canDo->get('core.admin')) {
			\JToolBarHelper::divider();
			\JToolBarHelper::preferences(CPH::getOption('com_name'),null,null, JText::_('MYSONGBOOKS_CFG_BTN_PERMISSIONS'));
		}

        //display the view
        parent::display($tpl);
	}


	/**
	 * Align entity definitions with database tables
	 */
	public function checkAndUpdateTables() {
        InterfaceHelper::addButtonsToToolBar([
			["core.manage", "configurations.display", 'back', JText::_('MYSONGBOOKS_BACK')],
            ["core.manage", "configurations.checkAndUpdateTables", 'refresh', JText::_('MYSONGBOOKS_CFG_BTN_SYNCDB')]
		]);
        $DBUH = new DatabaseUpdaterHelper();
        $DBUH->update(true);//true is for verbose
        parent::display("empty");
	}

}
