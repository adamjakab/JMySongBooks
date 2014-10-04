<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
/**
 * this file is included inside the render method of the \JLayoutFile
 * @var \JLayoutFile $this
 */

/**
 * when rendering the $displayData param is available - this is the original
 * $displayData param passed to the original \JLayoutHelper::render method
 * @var \stdClass $displayData
 */

$fHtmlStr = '<div class="js-stools-field-filter">'.NL
	. '%s'.NL
	.'</div>'.NL;

foreach($displayData as $fName => $fData) {
	if($fName != "search" && $fName != "limits") {
		$fHtml = false;
		switch($fData["type"]) {
			case "select":
				/**
				 * select filters must always reset limitstart otherwise we might remain trapped on page where we
				 * cannot see records (limitstart=20 but page has 10 records) and no navigation would appare so
				 * we cannot even go to limitstart=0 ::: -> document.adminForm.limitstart.value=0;
				 * "document.adminForm.limitstart" must be checked because we might not have it in page
				 */
				$fHtml = \JHTML::_('select.genericlist',
					$fData["options"],
					'filter['.$fName.']',
					'onchange="if(document.adminForm.limitstart)document.adminForm.limitstart.value=0; document.adminForm.submit();" class="inputbox" size="1" ',
					'value',
					'text',
					$fData["value"],
					'filter_' . $fName
				);
				break;
			default:
				$fHtml = "unhandled type: " . $fData["type"];
				break;
		}
		if($fHtml!==false) {
			echo(sprintf($fHtmlStr, $fHtml));
		}
	}
}
