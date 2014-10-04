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

/** @var \Joomla\Registry\Registry $options */
$options = $this->getOptions();

$searchFilter = (isset($displayData->search)?$displayData->search:false);
$limitFilter = (isset($displayData->limits)?$displayData->limits:false);

//this is stupid and it does not work!!!
//$baseFilterCount = 0 + ($searchFilter?1:0) + ($limitFilter?1:0);
//$extraFilters = (count($displayData)-$baseFilterCount>0);//are there other filters apart from search and limits
$extraFilters = true;

$itemCountStr = '';
if(
	($totalRecords = $options->get("totalRecords", false)) !== false &&
	($filteredRecords = $options->get("filteredRecords", false)) !== false
	) {
	if ($totalRecords == $filteredRecords) {
		$itemCountStr = 'Records: ' . $totalRecords;
	} else {
		$itemCountStr = 'Records(filtered): ' . $filteredRecords . ' / ' . $totalRecords;
	}
}
?>
<?php if ($searchFilter) : ?>
	<label for="filter_search" class="element-invisible">
		<?php echo JText::_('MYSONGBOOKS_SEARCH_FILTER'); ?>
	</label>
	<div class="btn-wrapper input-append">
		<input
			type="text"
			name="filter[search]"
			id="filter_search"
			value="<?php echo $searchFilter["value"]; ?>"
			class="js-stools-search-string"
			placeholder="<?php echo $searchFilter["placeholder"]; ?>"
			/>
		<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('MYSONGBOOKS_SEARCH_FILTER_TOOLTIP'); ?>">
			<i class="icon-search"></i>
		</button>
	</div>
<?php endif; ?>

<?php if ($extraFilters) : ?>
	<div class="btn-wrapper hidden-phone">
		<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo JHtml::tooltipText('MYSONGBOOKS_SEARCH_TOOLS_TOOLTIP'); ?>">
			<?php echo JText::_('MYSONGBOOKS_SEARCH_TOOLS');?> <i class="caret"></i>
		</button>
	</div>
<?php endif; ?>

<div class="btn-wrapper">
	<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo JHtml::tooltipText('MYSONGBOOKS_SEARCH_FILTER_CLEAR_TOOLTIP'); ?>">
		<?php echo JText::_('MYSONGBOOKS_SEARCH_FILTER_CLEAR');?>
	</button>
</div>

<div class="btn-wrapper">
	<pre class="text-info item-count"><?php echo $itemCountStr;?></pre>
</div>

