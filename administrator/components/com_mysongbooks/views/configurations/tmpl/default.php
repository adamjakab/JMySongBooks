<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

defined('_JEXEC') or die();
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;
$cols = 0;
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
        <?php echo $this->getSidemenu(); ?>
	</div>

	<div id="j-main-container" class="span10">
		<table class="list table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th><?php echo JText::_('MYSONGBOOKS_CFG_TIT_PCODE');$cols++; ?></th>
					<th><?php echo JText::_('MYSONGBOOKS_CFG_TIT_PNAME');$cols++; ?></th>
					<th><?php echo JText::_('MYSONGBOOKS_CFG_TIT_PVALUE');$cols++; ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$paramGroups = CPH::getParamGroups();
			foreach ($paramGroups as $paramGroup):
				?>
				<tr class="header">
					<td colspan="<?php echo $cols; ?>"><?php echo \JText::_("MYSONGBOOKS_SETTING_PCAT_" . strtoupper($paramGroup)); ?></td>
				</tr>
				<?php
				$groupParams = CPH::getParamsInGroup($paramGroup);
				$i = 0;
				foreach ($groupParams as $paramKey => $groupParam):
                    $PKLINK = '<a name="paramChanger" data-paramkey="'.$paramKey.'" style="cursor: pointer;">' . $groupParam["label"] . '</a>';

					$paramValue = CPH::getOption($paramKey, true);
					switch($groupParam["type"]) {
						case "list":
							$paramValue = CPH::getParamValueNameFromList($paramKey, $paramValue);
							break;
					}
					?>
					<tr class="row<?php echo $i++ % 2; ?>">
						<td><?php echo $paramKey; ?></td>
						<td><?php echo $PKLINK; ?><br /><small><?php echo $groupParam["description"]; ?></small></td>
						<td class="pv pv_<?php echo $paramKey; ?>"><?php echo $paramValue; ?></td>
					</tr>
				<?php
				endforeach;//group params
			endforeach;//param groups
			?>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="option" value="<?php echo CPH::getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo CPH::getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>


<div id="bModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="bModalTitle" aria-hidden="true">
    <div class="modal-header">
        <h3 id="bModalTitle"></h3>
    </div>
    <div class="modal-body"></div>
    <div class="modal-msg"></div>
    <div class="modal-footer">
        <button class="btn btn-close" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-primary btn-save">Save</button>
    </div>
</div>


<script language="javascript" type="text/javascript">
    jQuery(function($) {
        var listTable = $("table.list");
        var adminForm = $("form#adminForm");
        var bModal = $("#bModal");
        var paramKey;


        $("a[name=paramChanger]", listTable).click(function() {
            paramKey = $(this).attr("data-paramkey");
            //console.log("clicked: " + paramKey);
            $('h3#bModalTitle', bModal).html("<?php echo JText::_('MYSONGBOOKS_CFG_MOD_PARAM'); ?>");
            $('.modal-body', bModal).html("");
            $('.modal-msg', bModal).html("");
            bModal.modal('show');

            $.post("index.php", {
                    option: $("input[name=option]", adminForm).val(),
                    task: "configurations.getParamEditForm",
                    layout: "json",
                    paramName: paramKey
                },
                function (data) {
                    var answer = elaborateJsonResponse(data, true);
                    if(answer) {
                        $('.modal-body', bModal).html(answer.result);
                    }
                }
            );
        });

        bModal.bind("show", function() {
            $("button.btn-save", bModal).click(function(e) {
                console.log("saving...");
                $('.modal-msg', bModal).html("");
                var newValue = $("input[name=paramValue], textarea[name=paramValue], select[name=paramValue]", bModal).val();
                $.post("index.php", {
                        option: $("input[name=option]", adminForm).val(),
                        task: "configurations.submitParamEditForm",
                        layout: "json",
                        paramName: paramKey,
                        paramValue: newValue
                    },
                    function (data) {
                        var answer = elaborateJsonResponse(data, false);
                        if(answer){
                            if (typeof answer.errors != "undefined") {
                                $('.modal-msg', bModal).html(answer.errors.message);
                            } else {
                                if($("input[name=paramValue], textarea[name=paramValue], select[name=paramValue]", bModal).prop("tagName") == "SELECT") {
                                    newValue = $("select[name=paramValue] option[value="+newValue+"]", bModal).html();
                                }
                                $("td.pv_"+paramKey, listTable).html(newValue);
                                bModal.modal('hide');
                            }
                        }
                    }
                );
            });
        });

        bModal.bind("hide", function() {$("button.btn-save", bModal).unbind();});

        var elaborateJsonResponse = function(data, showErrors) {
            var answer = (typeof data != "object" ? JSON.parse(data) : data);
            if (showErrors && typeof answer.errors != "undefined") {
                $('#bModalTitle', bModal).html("ERROR "+answer.errors.code);
                $('.modal-body', bModal).html(answer.errors.message);
                $('.modal-msg', bModal).html("File: "+answer.errors.file+"@"+answer.errors.line+"<br />"+JSON.stringify(answer.errors.trace)).css("color","#a00000").css("font-size","9px").css("word-wrap","break-word");
                $('.modal-footer', bModal).html("");
                answer = false;
            }
            return(answer);
        }
    });

    if(typeof Joomla != "undefined") {//when no toolbar buttons are set J! does NOT load its core.js which defines the Joomla object
        Joomla.submitbutton = function (pressbutton) {
            if (pressbutton == 'configurations.checkAndUpdateTables') {
                if (!confirm("<?php echo JText::_( 'MYSONGBOOKS_CFG_CHECKDB_CONFIRM' ); ?>")) {
                    return(false);
                }
            }
            submitform(pressbutton);
            return(true);
        }
    }

</script>