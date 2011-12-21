<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

	public function validate($userInput) {
		return (bool) $userInput;
	}

	public function render(array $params, CM_Form_Abstract $form) {
		$this->setTplParam('tabindex', isset($params['tabindex']) ? (int) $params['tabindex'] : null);
		$this->setTplParam('class', isset($params['class']) ? $params['class'] : null);
		$this->setTplParam('checked', $this->getValue() ? 'checked' : null);
		$this->setTplParam('label', isset($params['label']) ? $params['label'] : null);
	}
}
