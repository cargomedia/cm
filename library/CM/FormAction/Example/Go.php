<?php

class CM_FormAction_Example_Go extends CM_FormAction_Abstract {
	public function __construct() {
		parent::__construct('go');
	}

	public function setup(CM_Form_Abstract $form) {
		$this->required_fields = array('text', 'color');
		parent::setup($form);
	}

	public function process(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		//$response->reloadComponent();
		$response->addMessage(nl2br($this->_printVar($data)));
	}

	private function _printVar($var) {
		$str = '';
		if (is_object($var)) {
			$str .= get_class($var);
		} elseif (is_array($var)) {
			$str .= '{';
			if (!empty($var)) {
				$str .= PHP_EOL;
			}
			foreach ($var as $key => $value) {
				$str .= $key . ': ' . $this->_printVar($value) . PHP_EOL;
			}
			$str .= '}';
		} else {
			$str .= (string) $var;
		}
		return $str;
	}
}
