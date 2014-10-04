<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\InterfaceHelper as IFH;
use MySongBooks\Core\Helpers\MusicHelper;
use MySongBooks\Core\Repository\ChordRepository;
use MySongBooks\Core\Entity\Chord;
use MySongBooks\Core\Entity\ChordLayout;

/** @var MySongBooksViewChords $this */

//Add JTab support
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/raphael.js");
IFH::addAdditionalHeaderIncludes("js", "/media/".CPH::getOption("com_name")."/js/jTab.js");

/** @var \Doctrine\ORM\EntityManager $em */
$em = CPH::getOption("EntityManager");
/** @var ChordRepository $chordRepo */
$chordRepo = $em->getRepository('MySongBooks\Core\Entity\Chord');



?>
<h1>Chords</h1>

<?php
$SD = new stdClass();
$FD = new stdClass();
$FD->root_note = ["whereSql" => "i.root_note = ?"];

for($ni=1;$ni<=12;$ni++):
	$noteName = MusicHelper::getNoteLetterForChromaticScaleC($ni);
?>

	<div class="cordsList">
		<h3>Chords in <?php echo $noteName; ?></h3>
		<?php
		$SD->filters["root_note"] = $ni;
		$chords = $chordRepo->getFilteredItems($SD, $FD);
		/** @var Chord $chord */
		foreach ($chords as $i => $chord):
			$chordLayouts = $chord->getChordLayouts();
			$chordLayoutScheme = false;
			if(!$chordLayouts->isEmpty()) {
				/** @var ChordLayout $chordLayout */
				$chordLayout = $chordLayouts->first();
				$chordLayoutScheme = $chordLayout->getScheme();
			}
		?>
			<div class="chordbox">
				<h5><?php echo $chord->getName(); ?></h5>
				<h6><?php echo $chord->getAlternativeNames(); ?></h6>
				<?php if($chordLayoutScheme): ?>
					<div class="jtab" data-jtab-scheme="<?php echo $chordLayoutScheme; ?>"></div>
				<?php endif; ?>
			</div>
		<?php
		endforeach;
		?>
		<br class="clr">
	</div>
<?php
endfor;
?>





<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($){
		$(".jtab").jTab({
			debug: false,
			showCode: false,
		});
	});
</script>