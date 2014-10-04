<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Joomla;
defined('_JEXEC') or die();
use MySongBooks\Core\Exception\Exception;
use MySongBooks\Core\Helpers\ComponentParamHelper as CPH;

/**
 * Class JController
 * @package MySongBooks\Core\Joomla
 */
class JController extends \JControllerAdmin {
	/**
	 * @param array $config
	 */
	function __construct($config = []) {
		parent::__construct($config);
        $this->registerTask("delete","display");
		$this->registerTask("publish","display");
		$this->registerTask("unpublish","display");
		$this->registerTask("archive","display");
		$this->registerTask("trash","display");
	}

    /** The default display function that suits most basic tasks
     * @param bool $cachable
     * @param array $urlparams
     * @return JController
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = []) {
        $JI = new \JInput;
        $tmpl = $JI->getString("tmpl", "default");
        $layout = strtolower($JI->getString("layout", "html"));

	    /**
	     * View file naming and class naming different layouts:
	     * layout   layout specific class prefix    class name               file name
	     * -------------------------------------------------------------------------------------
	     * html         none                        MySongBooksViewDummy           view.html.php
	     * json         Json                        MySongBooksViewJsonDummy       view.json.php
	     * xml          Xml                         MySongBooksViewXmlDummy        view.xml.php
	     */
	    $prefix = COMPONENT_INSTANCE_NAME_MYSONGBOOKS."View".($layout!="html"?ucfirst($layout):"");
	    /** @var JView $view */
        $view = $this->getView(CPH::getOption("controller"), $layout, $prefix);
	    $view->setController($this);
		$view->setLayout($tmpl);

        //call the task method on the view
		if (method_exists($view, CPH::getOption('task'))) {
			call_user_func([$view, CPH::getOption('task')]);
		} else {
			if($layout != "json") {
				throw new Exception("An undefined task(".CPH::getOption('task').") requested on "
					."view='".CPH::getOption('view')."' layout='".$layout."'");
			} else {
				//normally json requests are for ajax calls so it's better to craft json output
				$output = new \stdClass();
				$output->errors = ["An undefined task(".CPH::getOption('task').") requested on "
					."view='".CPH::getOption('view')."' layout='".$layout."'"];
				echo json_encode($output);
				\JFactory::getApplication()->close();
			}
		}
        return($this);
	}
}

