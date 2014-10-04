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
 * when rendering the $displayData param is available -
 * this is the original $displayData param passed to the \JLayoutHelper::render method
 * @var stdClass $displayData
 */
if(!$displayData) {return;}

/** @var \Joomla\Registry\Registry $options */
$options = $this->getOptions();

//SHOW FILTERS AUTOMATICALLY IF RECORDS ARE FILTERED === FILTERS ARE SET
$filtersHidden = true;
if(
	($totalRecords = $options->get("totalRecords", false)) !== false &&
	($filteredRecords = $options->get("filteredRecords", false)) !== false
) {
	if ($totalRecords != $filteredRecords) {
		$filtersHidden = false;
	}
}

//echo '<pre>DD:'.print_r($displayData, true).'</pre>';
//echo '<pre>OPT:'.print_r($options, true).'</pre>';


$jsOptions = [
	"filtersHidden" => $filtersHidden,
	"defaultLimit" => 25,
	"searchFieldSelector" => "#filter_search",
	/*"orderFieldSelector" => "#list_fullordering",*/
	"formSelector" => "#adminForm"
];
\JHtml::_('searchtools.form', "#adminForm", $jsOptions);
?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<?php echo \JLayoutHelper::render('filterbar.search', $displayData, '', $options); ?>
		</div>
		<div class="js-stools-container-list hidden-phone hidden-tablet">
			<?php echo \JLayoutHelper::render('filterbar.limits', $displayData, '', $options); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters hidden-phone clearfix" style="<?php echo(!$filtersHidden?'display:block':'');?>">
		<?php echo \JLayoutHelper::render('filterbar.filters', $displayData, '', $options); ?>
	</div>
</div>
