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

/** @var $JI \JInput */
$JI = \JFactory::getApplication()->input;
$songid = $JI->getInt("sid", 0);

/** @var \Doctrine\ORM\EntityManager $em */
$em = CPH::getOption("EntityManager");
/** @var SongRepository $songRepo */
$songRepo = $em->getRepository('MySongBooks\Core\Entity\Song');
/** @var Song $song */
$song = $songRepo->find($songid);
if(!$song) {
	$song = new Song();
}
//--------------------

?>
<div class="toolbar">
	<h1>Editing song: <?php echo $song->getTitle(); ?></h1>
	<ul>
		<li>
			<button name="formsubmit" data-action-task="<?php echo CPH::getOption("controller"); ?>.save" class="btn btn-success" type="button">Save</button></a>
		</li>
		<li>
			<button name="formsubmit" data-action-task="<?php echo CPH::getOption("controller"); ?>.details" class="btn" type="button">Cancel</button></a>
		</li>
	</ul>
	<br class="clr" />
</div>

<form action="<?php echo JRoute::_('index.php?sid='.$songid); ?>" method="post" name="adminForm" id="adminForm">
	<div class="form-vertical">
		<div class="row-fluid">
			<div class="span8">
				<fieldset class="form-vertical">
					<?php
						echo IFH::getInputField('content', JText::_('MYSONGBOOKS_TIT_CONTENT'), $song->getContent(), ["required"=>false,"tooltip"=>'Song lyrics and chord notations',"class"=>"input-xxlarge", "rows"=>20], "textarea");
					?>
				</fieldset>
			</div>
			<div class="span4">
				<fieldset class="form-vertical">
					<?php
						echo IFH::getInputField('title', JText::_('MYSONGBOOKS_TIT_TITLE'), $song->getTitle(), ["required"=>true,"tooltip"=>'The song title',"class"=>"input-xlarge"]);
						echo IFH::getInputField('author', JText::_('MYSONGBOOKS_TIT_AUTHOR'), $song->getAuthor(), ["required"=>false,"tooltip"=>'Who wrote this song?',"class"=>"input-xlarge"]);

						echo IFH::getInputField('origin', JText::_('MYSONGBOOKS_TIT_ORIGIN'), $song->getOrigin(), ["required"=>false,"tooltip"=>'Where did you find this song?',"class"=>"input-xlarge"]);
						echo IFH::getInputField('origin_reference', JText::_('MYSONGBOOKS_TIT_ORIGIN_REFERENCE'), $song->getOriginReference(), ["required"=>false,"tooltip"=>'Specify a page or a song number in the original source.',"class"=>"input-xlarge"]);
						echo IFH::getInputField('origin_note', JText::_('MYSONGBOOKS_TIT_ORIGIN_NOTE'), $song->getOriginNote(), ["required"=>false,"tooltip"=>'Any notes or copyright info in the original source.',"class"=>"input-xlarge", "rows"=>5], "textarea");
					?>
				</fieldset>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo IFH::getInputField('id', null, $songid, null, "hidden"); ?>
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