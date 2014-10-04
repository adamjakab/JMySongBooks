<?php
/**
 * @package    MySongBooks
 * @author     Adam Jakab {@link http://devshed.jakabadambalazs.com}
 * @author     Created on 18-Jul-2014
 * @license    GNU/GPL
 */

namespace MySongBooks\Core\Exception;
defined('_JEXEC') or die('Restricted access');

/**
 * Class Exception
 */
class Exception extends \Exception {
	/**
	 * @param string     $message
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($message="", $code=0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}

    /**
     * @return string
     */
    public function getFormattedErrorMessage() {
		$answer = '';
		$answer .= '<div class="alert alert-danger"><strong>('.$this->getCode().") ".\JText::_($this->getMessage()).'</strong></div>';
	    $answer .= '<div class="alert alert-info">Exception type: '.get_class($this).'</div>';
        $answer .= '<div class="alert alert-info">Exception was thrown at line '.$this->getLine().' in file: '.$this->getFile().'</div>';
        $answer .= '<pre>';
            foreach($this->getTrace() as $i => $traceObj) {
                $answer .= '<h2 style="float:left;width:30px; line-height:65px;">'.$i.'</h2>'
                    .(isset($traceObj["file"])?'file: '.$traceObj["file"].BR:'')
                    .(isset($traceObj["line"])?'line: '.$traceObj["line"].BR:'')
                    .(isset($traceObj["function"])?'function: '.$traceObj["function"].BR:'')
                    .(isset($traceObj["class"])?'class: '.$traceObj["class"].BR:'')
                    .(isset($traceObj["args"])?'arguments: '.json_encode($traceObj["args"]).BR:'')
                    .BRNL;
            }
        $answer .= '</pre>';
		return($answer);
	}

    /**
     * @return \stdClass
     */
    public function getErrorObject() {
        $answer = new \stdClass();
        $answer->code = $this->getCode();
        $answer->message = $this->getMessage();
        $answer->file = $this->getFile();
        $answer->line = $this->getLine();
        $answer->trace = $this->getTrace();
        return($answer);
    }
}