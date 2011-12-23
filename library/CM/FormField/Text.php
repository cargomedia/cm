<?php

class CM_FormField_Text extends CM_FormField_Abstract {

	public function __construct($name, $lengthMin = null, $lengthMax = null) {
		parent::__construct($name);
		$this->_options['lengthMin'] = isset($lengthMin) ? (int) $lengthMin : null;
		$this->_options['lengthMax'] = isset($lengthMax) ? (int) $lengthMax : null;
	}
	
	public function validate($userInput) {
		if (isset($this->_options['lengthMax']) && strlen($userInput) > $this->_options['lengthMax']) {
			throw new CM_Exception_FormFieldValidation('Too long');
		}
		if (isset($this->_options['lengthMin']) && strlen($userInput) < $this->_options['lengthMin']) {
			throw new CM_Exception_FormFieldValidation('Too short');
		}
		return $userInput;
	}

	public function prepare(array $params) {
		$this->setTplParam('tabindex', isset($params['tabindex']) ? $params['tabindex'] : null);
		$this->setTplParam('class', isset($params['class']) ? $params['class'] : null);
		$this->setTplParam('placeholder', isset($params['placeholder']) ? $params['placeholder'] : null);
	}
}
