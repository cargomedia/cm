<?php

class CM_FormField_Integer extends CM_FormField_Abstract {

	/**
	 * @param string $name OPTIONAL
	 * @param int $min OPTIONAL
	 * @param int $max OPTIONAL
	 * @param int $step OPTIONAL
	 */
	public function __construct($name = 'slider', $min = 0, $max = 100, $step = 1) {
		parent::__construct($name);
		$this->_options['min'] = (int) $min;
		$this->_options['max'] = (int) $max;
		$this->_options['step'] = (int) $step;
	}

	public function prepare(array $params, CM_Form_Abstract $form) {
		$this->setTplParam('class', isset($params['class']) ? (string) $params['class'] : null);
	}

	public function validate($userInput) {
		if (!is_numeric($userInput)) {
			throw new CM_Exception_FormFieldValidation('Invalid number');
		}
		$value = (int) $userInput;
		if ($value < $this->_options['min'] || $value > $this->_options['max']) {
			throw new CM_Exception_FormFieldValidation('Value not in range.');
		}
		return $value;
	}
}
