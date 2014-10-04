<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use \MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Entity\Chord;
use MySongBooks\Core\Entity\ChordLayout;

/** @var MySongBooksViewChords $this */

/** @var $JI \JInput */
$JI = \JFactory::getApplication()->input;
$cid = $JI->getInt("cid", null);
/** @var Chord $chord */
$chord = $this->repository->find($cid);
if(!$chord) {
	throw new Exception("Chord not found with id: $cid");
}

//Add JTab support
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/raphael.js");
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/jTab.js");


?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo CPH::getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="id" value="<?php echo $chord->getId(); ?>"/>
	<input type="hidden" name="deleteid" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<h1><?php echo $chord->getAllNames(); ?></h1>
<p>Number of layouts: <?php echo $chord->countChordLayouts(); ?></p>



<?php
if($chord->countChordLayouts()):
	$chordLayouts = $chord->getChordLayouts();
	/** @var ChordLayout $chordLayout */
	foreach($chordLayouts as $chordLayout):
?>
	<div class="chordbox">
		<div class="jtab" data-jtab-scheme="<?php echo $chordLayout->getScheme(); ?>" data-jtab-id="<?php echo $chordLayout->getId(); ?>"></div>
		<button class="btn btn-mini btn-primary pull-right chord-edit">Edit</button>
		<button class="btn btn-mini pull-left chord-delete">Delete</button>
	</div>
<?php
	endforeach;
endif;
?>

<div id="bModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="bModalTitle" aria-hidden="true">
	<div class="modal-header"><h3>&nbsp;</h3></div>
	<div class="modal-body"></div>
	<div class="modal-msg"></div>
	<div class="modal-footer">
		<button class="btn btn-close" data-dismiss="modal" aria-hidden="true">Close</button>
		<button class="btn btn-primary btn-save">Save</button>
	</div>
</div>


<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($){
		var bModal = $("#bModal");
		var adminForm = $("form#adminForm");
		var jTabDiv;



		$(".jtab").jTab({
			debug: true,
			showCode: true,
		});



		$("button.chord-edit").click(function() {
			jTabDiv = $(".jtab", $(this).parent(".chordbox"));
			var chordScheme = jTabDiv.attr("data-jtab-scheme");
			$('.modal-header h3', bModal).html("<?php echo JText::_('MYSONGBOOKS_CHORD_EDITOR'); ?>");
			var html = '<div class="chordbox jtab" data-jtab-scheme="'+chordScheme+'"></div>'
				+ '<input name="chordScheme" value="'+chordScheme+'" class="input-xxlarge input-large-text"/>';
			$('.modal-body', bModal).html(html);
			$('.modal-msg', bModal).html("");
			$('.btn-save', bModal).show();//hidden when errors occur
			bModal.modal('show');
			$(".jtab", bModal).jTab();
			$("input[name=chordScheme]", bModal).bind("keyup change", function() {
				var isValid = $(".jtab", bModal).jTab("refresh", $(this).val());
				if(isValid) {
					$('.btn-save', bModal).show();
				} else {
					$('.btn-save', bModal).hide();
				}
			});
		});


		bModal.bind("show", function() {
			$("button.btn-save", bModal).click(function(e) {
				$('.modal-msg', bModal).html("");
				var newScheme = $("input[name=chordScheme]", bModal).val();
				//console.log("saving: " + newScheme);
				$.post("index.php", {
						option: $("input[name=option]", adminForm).val(),
						task: "chords.saveLayoutScheme",
						layout: "json",
						id: jTabDiv.attr("data-jtab-id"),
						scheme: newScheme
					},
					function (data) {
						var answer = elaborateJsonResponse(data, true);
						if(answer){
							if (typeof answer.errors != "undefined") {
								$('.modal-msg', bModal).html(answer.errors.message);
							} else {
								//console.log("ok");
								bModal.modal('hide');
								jTabDiv.jTab("refresh", newScheme);
							}
						}
					}
				);
			});
		});

		bModal.bind("hide", function() {$("button.btn-save", bModal).unbind();});

		$("button.chord-delete").click(function() {
			if(confirm("Are you sure you want to delete this chord layout?")) {
				jTabDiv = $(".jtab", $(this).parent(".chordbox"));
				$("input[name=deleteid]", adminForm).val(jTabDiv.attr("data-jtab-id"));
				Joomla.submitbutton('chords.deleteChordlayout');
			}
		});

		var elaborateJsonResponse = function(data, showErrors) {
			var answer = (typeof data != "object" ? JSON.parse(data) : data);
			if (showErrors && typeof answer.errors != "undefined") {
				$('.modal-header h3', bModal).html("ERROR "+answer.errors.code);
				$('.modal-body', bModal).html(answer.errors.message);
				$('.modal-msg', bModal).html("File: "+answer.errors.file+"@"+answer.errors.line);
				$('.btn-save', bModal).hide();
				answer = false;
			}
			return(answer);
		}

	});
</script>