<?php

class CM_FormField_Boolean extends CM_FormField_Abstract {

	public function validate($userInput) {
		return (bool) $userInput;
	}

	public function render(array $params, CM_Form_Abstract $form) {
		$params['tabindex'] = isset($params['tabindex']) ? (int) $params['tabindex'] : null;
		$params['class'] = isset($params['class']) ? $params['class'] : null;

		$params['checked'] = null;
		if ($this->_getValue()) {
			$params['checked'] = 'checked';
		}
		
		return parent::render($params, $form);
	}
}
