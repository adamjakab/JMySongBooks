<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
JHtml::_('behavior.tooltip');
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Entity\Chord;

/** @var MySongBooksViewChords $this */

/** @var \stdClass $state */
$state = $this->getStateData();

/** @var \JPagination $pagination */
$pagination = $this->getPagination();

/** @var array $items */
$items = $this->getItems();

//ORDERING
$listOrder	= $this->escape($state->list["ordering"]);
$listDirn	= $this->escape($state->list["direction"]);

?>



<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->getSidemenu(); ?>
    </div>

    <div id="j-main-container" class="span10">
	    <?php echo $this->getFilters(); ?>

	    <?php if (!count($items)) : ?>
		    <div class="alert alert-no-items">
			    <?php echo JText::_('MYSONGBOOKS_NO_MATCHING_RESULTS'); ?>
		    </div>
	    <?php else : ?>
		    <table class="list table table-striped table-bordered table-hover" id="orderableTable">
			    <thead>
			    <tr>
				    <th style="width:1%;" class="nowrap center hidden-phone">
					    <?php echo JHtml::_('grid.checkall'); ?>
				    </th>
				    <th style="width:10%;" class="nowrap center">
					    <?php echo JHtml::_('grid.sort', 'MYSONGBOOKS_TIT_NAME', 'i.name', $listDirn, $listOrder, CPH::getOption("ctrl.task")); ?>
				    </th>
				    <th class="nowrap center">
					    <?php echo JText::_('MYSONGBOOKS_TIT_ALTNAMES'); ?>
				    </th>
				    <th style="width:1%;" class="nowrap center hidden-phone">
					    <?php echo JHtml::_('grid.sort', 'MYSONGBOOKS_TIT_ROOT_NOTE', 'i.root_note', $listDirn, $listOrder, CPH::getOption("ctrl.task")); ?>
				    </th>
				    <th style="width:15%;" class="nowrap center hidden-phone">
					    <?php echo JHtml::_('grid.sort', 'MYSONGBOOKS_TIT_CHORD_TYPE', 'i.type', $listDirn, $listOrder, CPH::getOption("ctrl.task")); ?>
				    </th>
				    <?php /*
				    <th style="width:1%;" class="nowrap center hidden-phone">
					    <?php echo JHtml::_('grid.sort', 'MYSONGBOOKS_TIT_STATUS', 'i.published', $listDirn, $listOrder, CPH::getOption("ctrl.task")); ?>
				    </th>
                    */ ?>
				    <th style="width:1%;" class="nowrap center hidden-phone">
					    #
				    </th>
				    <th style="width:1%;" class="nowrap center hidden-phone">
					    <?php echo JHtml::_('grid.sort', 'MYSONGBOOKS_TIT_ID', 'i.id', $listDirn, $listOrder, CPH::getOption("ctrl.task")); ?>
				    </th>
			    </tr>
			    </thead>
			    <tbody>
			    <?php
			    /** @var Chord $item */
			    foreach ($items as $i => $item):
				    /*
				    if(is_array($item)) {
					    echo "!!!ARRAY: " . json_encode($item);
					    $item = $item[0];
				    }*/

				    $detailsUri = \JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.details&cid='.$item->getId());
				    $detailsLink = '<a href="'.$detailsUri.'">'.$item->getName().'</a>';

				    $chordType = $item->getType();
				?>
				    <tr class="" sortable-group-id="<?php echo "default";?>" item-id="<?php echo $item->getId();?>" parents="" level="1">

					    <td class="center hidden-phone"><?php echo JHtml::_('grid.id', $i, $item->getId()); ?></td>
					    <td><?php echo $detailsLink; ?></td>
					    <td><?php echo $item->getAlternativeNames(); ?></td>
					    <td><?php echo $item->getRootNoteName(); ?></td>
					    <td><?php echo $chordType->getName(); ?></td>
						<?php /* <td><?php echo IFH::getEntityListingStateControlBox(CPH::getOption("controller"), $i, $item); ?></td> */ ?>
					    <td><?php echo $item->countChordLayouts(); ?></td>
					    <td><?php echo $item->getId(); ?></td>
				    </tr>
			    <?php endforeach; ?>
			    </tbody>
		    </table>
		    <?php echo $pagination->getListFooter(); ?>
	    <?php endif; ?>
	</div>

	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo CPH::getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>


<script language="javascript" type="text/javascript">
	jQuery(function($) {/**/});
</script>