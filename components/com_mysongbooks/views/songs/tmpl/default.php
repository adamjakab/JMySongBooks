<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Helpers\MusicHelper;
use MySongBooks\Core\Repository\SongRepository;
use MySongBooks\Core\Entity\Song;

/** @var MySongBooksViewSongs $this */

/** @var \Doctrine\ORM\EntityManager $em */
$em = CPH::getOption("EntityManager");
/** @var SongRepository $songRepo */
$songRepo = $em->getRepository('MySongBooks\Core\Entity\Song');

$SD = new stdClass();
$FD = new stdClass();
$SD->list["ordering"] = 'i.origin_reference';
$SD->list["direction"] = 'ASC';
//$SD->filters["root_note"] = $ni;
//$FD->root_note = ["whereSql" => "i.root_note = ?"];

$songs = $songRepo->getFilteredItems($SD, $FD);
//$songs = $songRepo->findAll();



?>
<div class="toolbar">
	<h1>My Songs</h1>
	<ul>
		<li>
			<button name="formsubmit" data-action-task="<?php echo CPH::getOption("controller"); ?>.edit" class="btn btn-success" type="button">Add New Song</button></a>
		</li>
	</ul>
	<br class="clr" />
</div>


<div>
	<table class="list table table-striped table-bordered table-hover" id="orderableTable">
		<thead>
		<tr>
			<th class="nowrap center">
				<?php echo JText::_('MYSONGBOOKS_TIT_TITLE'); ?>
			</th>
			<th style="width:1%;" class="nowrap center hidden-phone">
				&nbsp;
			</th>
			<th style="width:25%;" class="nowrap center hidden-phone">
				<?php echo JText::_('MYSONGBOOKS_TIT_ORIGIN'); ?>
			</th>
			<th style="width:1%;" class="nowrap center hidden-phone">
				<?php echo JText::_('MYSONGBOOKS_TIT_REF'); ?>
			</th>
			<th style="width:1%;" class="nowrap center hidden-phone">
				<?php echo JText::_('MYSONGBOOKS_TIT_ID'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		/** @var Song $song */
		foreach ($songs as $song):
			$detailsUri = \JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.details&sid='.$song->getId());
			$detailsLink = '<a href="'.$detailsUri.'">'.$song->getTitle().'</a>';

			?>
			<tr>
				<td><?php echo $detailsLink; ?></td>
				<td>&nbsp;</td>
				<td><?php echo $song->getOrigin(); ?></td>
				<td><?php echo $song->getOriginReference(); ?></td>
				<td><?php echo $song->getId(); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($) {
		var toolbar = $(".toolbar");
		var adminForm = $("#adminForm");
		//toolbar form button actions
		var submitbuttons = $("button[name=formsubmit]", toolbar);
		submitbuttons.on("click", function() {
			$("input[name=task]", adminForm).val($(this).attr("data-action-task"));
			adminForm.submit();
		});
	});
</script>