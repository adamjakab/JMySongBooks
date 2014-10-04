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
use MySongBooks\Core\Helpers\InterfaceHelper;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
use MySongBooks\Core\Helpers\MusicHelper;

/** @var MySongBooksViewChords $this */

//SELECT PUBLISH STATES
$PUBSTATES = InterfaceHelper::getSelectOptionsPublishedStates();
$ROOTNOTES = MusicHelper::getSelectOptions_ChromaticScaleC();
$CHORDTYPES = MusicHelper::getSelectOptions_ChordTypes();
//$stateData to use filter values for default values
$stateData = $this->getStateData();


?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="form-inline form-inline-header">
    <?php
        //echo InterfaceHelper::getInputField('name', JText::_('MYSONGBOOKS_TIT_NAME'), $this->getEditDataForProp("name"), ["required"=>true,"tooltip"=>'The most common name of the chord',"class"=>"input-xxlarge input-large-text"]);
        echo InterfaceHelper::getInputField('root_note', JText::_('MYSONGBOOKS_TIT_ROOT_NOTE'), $this->getEditDataForProp("root_note",(isset($stateData->filters["root_note"])?$stateData->filters["root_note"]:"")), ["options"=>$ROOTNOTES,"class"=>"chzn-container"], "select");
        echo InterfaceHelper::getInputField('type', JText::_('MYSONGBOOKS_TIT_CHORD_TYPE'), $this->getEditDataForProp("type",(isset($stateData->filters["type"])?$stateData->filters["type"]:"")), ["options"=>$CHORDTYPES,"class"=>"chzn-container"], "select");
    ?>
    </div>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'bsTab', ['active' => 'details']); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'bsTab', 'details', JText::_('MYSONGBOOKS_DETAILS', true)); ?>
        <div class="row-fluid">
            <div class="span9">
                <fieldset class="form-horizontal">
	            <?php
	                //echo InterfaceHelper::getInputField('alt_names', JText::_('MYSONGBOOKS_TIT_ALTNAMES'), implode(", ", $this->getEditDataForProp("alt_names")), ["required"=>false,"tooltip"=>'Other names or notations of the chord',"class"=>"input-xxlarge", "rows"=>3], "textarea");
	            ?>
                </fieldset>
            </div>
            <div class="span3">
                <fieldset class="form-vertical">
                <?php
	                //PUBLISHED STATE
	                echo InterfaceHelper::getInputField('published', 'Published', $this->getEditDataForProp("published"), ["options"=>$PUBSTATES,"class"=>"chzn-container chzn-color-state"], "select");
                ?>
                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
    <input type="hidden" name="task" value=""/>
    <?php echo InterfaceHelper::getInputField('id', null, $this->getEditDataForProp("id"), null, "hidden"); ?>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        Joomla.submitform(task, document.getElementById('adminForm'));
    }
</script>