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
 * @var stdClass $displayData
 */

$limitFilter = (isset($displayData->limits)?$displayData->limits:false);
$limitFilterHtml = false;
if($limitFilter) {
	$limitFilterHtml =  \JHTML::_('select.genericlist',
		$limitFilter["options"],
		'list[limit]',
		'onchange="document.adminForm.submit();" class="inputbox input-mini" size="1" ',
		'value',
		'text',
		$limitFilter["value"] ,
		'list_limit'
	);
}
?>
<?php if ($limitFilterHtml) : ?>
	<div class="ordering-select hidden-phone">
		<div class="js-stools-field-list">
			<?php echo $limitFilterHtml; ?>
		</div>
	</div>
<?php endif; ?>
