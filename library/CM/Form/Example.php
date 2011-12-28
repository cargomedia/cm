<?php

class CM_Form_Example extends CM_Form_Abstract {
	public function setup() {
		$this->registerField(new CM_FormField_Text('text'));
		$this->registerField(new CM_FormField_Integer('int', -10, 20, 2));
		$this->registerField(new CM_FormField_Distance('locationSlider'));
		$this->registerField(new CM_FormField_Location('location', CM_Location::LEVEL_COUNTRY, $this->getField('locationSlider')));
		$this->registerField(new CM_FormField_FileImage('image', 2));
		$this->registerField(new CM_FormField_Color('color'));

		$this->registerAction(new CM_formExample_Go());
	}

	public function renderStart(array $params = null) {
		if (!empty($params['viewer'])) {
			if ($color = $params['viewer']->getProfile()->getBackgroundColor()) {
				$this->getField('color')->setValue($color);
			}
		}
		if ($locationGuess = CM_Location::findByIp(CM_Request_Abstract::getIp())) {
			$this->getField('location')->setValue($locationGuess);
		}
	}
}

class CM_formExample_Go extends CM_FormAction_Abstract {
	public function __construct() {
		parent::__construct('go');
	}

	public function setup(CM_Form_Abstract $form) {
		$this->required_fields = array('text', 'color');
		parent::setup($form);
	}

	public function process(array $data, CM_Response_Component_Form $response, CM_Form_Abstract $form) {
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
