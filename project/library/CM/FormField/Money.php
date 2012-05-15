<?php

class CM_FormField_Money extends CM_FormField_Text {

	/**
	 * @param string $name
	 * @param null $min
	 * @param null $max
	 */
	public function __construct($name, $min = null, $max = null) {
		parent::__construct($name);
		$this->_options['lengthMin'] = isset($min) ? (int) $min : null;
		$this->_options['lengthMax'] = isset($max) ? (int) $max : null;
	}

	public function validate($userInput, CM_Response_Abstract $response) {
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
