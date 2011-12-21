<?php

class CM_FormField_Date extends CM_FormField_Abstract {
	
	protected $_range = array();

	public function validate($userInput) {
		$dd = trim($userInput['day']);
		$mm = trim($userInput['month']);
		$yy = trim($userInput['year']);

		if (!$dd || !$mm || !$yy) {
			throw new CM_FormFieldValidationException("day, month or year not set");
		}
		return new DateTime($yy . '-' . $mm . '-' . $dd);
	}
	
	public function render(array $params, CM_Form_Abstract $form) {	
		$this->setTplParam('class', isset($params['class']) ? $params['class'] : null);

		$value = $this->getValue();
		$this->setTplParam('yy', $value ? $value->format('Y') : null);
		$this->setTplParam('mm', $value ? $value->format('m') : null);
		$this->setTplParam('dd', $value ? $value->format('d') : null);

		$this->setTplParam('minYear', $this->_range['min']);
		$this->setTplParam('maxYear', $this->_range['max']);
	}
}
