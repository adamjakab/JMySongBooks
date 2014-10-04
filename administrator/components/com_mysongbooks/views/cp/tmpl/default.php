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

/** @var MySongBooksViewCp $this */

//get data from view
$controlPanelInfo = $this->getCpInfo();


?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->getSidemenu(); ?>
    </div>

    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="well well-small">
                <h2 class="module-title nav-header"><?php echo \JText::_("MYSONGBOOKS_CP_INFO_PANELNAME"); ?></h2>
                <?php
                    $output = '';
                    $output .= '<table class="table table-striped table-bordered ">';
                    $k = 0;
                    foreach ($controlPanelInfo as &$cpi) {
                        $output .= '<tr class="row' . $k = 1 - $k . '">';
                        $output .= '<td style="min-width:200px;">' . $cpi["key"] . '</td>';
                        $output .= '<td>' . $cpi["value"] . '</td>';
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                    echo $output;
                ?>
            </div>
        </div>
	</div>

	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo CPH::getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
