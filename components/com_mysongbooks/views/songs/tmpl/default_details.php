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

//Add JTab support
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/raphael.js");
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/jTab.js");

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
	$this->setRedirect(\JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display', false));
	return;
}
//--------------------
$backUri = \JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.display');
$editUri = \JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.edit&sid='.$song->getId());
$printUri = \JRoute::_('index.php?option='.CPH::getOption("com_name").'&task='.CPH::getOption("controller").'.printit&sid='.$song->getId());

$songMarkup = MusicHelper::getSongMarkup($song->getContent());

$showToolbar = (CPH::getOption("ctrl.task") !== CPH::getOption("controller").'.printit');//are we on the print view


?>


<?php if($showToolbar): ?>
	<div class="toolbar">
		<h1><a href="<?php echo $backUri; ?>"><button class="btn btn-info" type="button">Back to songs</button></a></h1>
		<ul>
			<li>
				<a href="<?php echo $editUri; ?>"><button class="btn btn-warning" type="button">Edit</button></a>
				<a href="<?php echo $printUri; ?>" target="MSBP"><button class="btn btn-primary" type="button">Print</button></a>
			</li>
		</ul>
		<br class="clr" />
	</div>
<?php endif; ?>

<div class="song">
	<div class="song-header row-fluid">
		<div class="song-origin-box">
			<div class="song-origin-reference"><?php echo $song->getOriginReference(); ?></div>
			<div class="song-origin"><?php echo $song->getOrigin(); ?></div>
		</div>
		<h1 class="song-title"><?php echo $song->getTitle(); ?></h1>
		<h5 class="song-author"><?php echo $song->getAuthor(); ?></h5>
		<p class="song-origin-note"><?php echo $song->getOriginNote(); ?></p>

	</div>

	<div class="song-body row-fluid">
		<div class="song-body-lft span10">
			<div class="lyrics">
				<?php echo $songMarkup->markUp; ?>
			</div>
		</div>
		<div class="song-body-rgt span2">
			<div class="chords">
				<?php
					foreach($songMarkup->idenfifiedChords as $IC):
						/*todo: there should be an option for this - skipping all major and minor chords (we know all these)*/
						if (!in_array($IC["chordIdentificator"], ["","-"])) :
				?>

					<div class="chordbox">
						<h5><?php echo $IC["chordName"]; ?></h5>
						<h6><?php echo $IC["chordAltNames"]; ?></h6>
						<?php if(isset($IC["chordLayoutScheme"])): ?>
							<div class="jtab" data-jtab-scheme="<?php echo $IC["chordLayoutScheme"]; ?>"></div>
						<?php endif; ?>
					</div>
				<?php
						endif;
					endforeach;
				?>
				<br class="clr" />
			</div>
		</div>
	</div>

	<div class="song-footer row-fluid">
		&nbsp;
	</div>
</div>




<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($){
		$(".jtab").jTab({
			debug: false,
			showCode: false,
		});
	});
</script>