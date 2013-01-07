<?php

class CM_FormField_Set_Select extends CM_FormField_Set {

	const DISPLAY_SELECT = 'select';
	const DISPLAY_RADIOS = 'radios';

	public function validate($userInput, CM_Response_Abstract $response) {
		if (!in_array($userInput, $this->_getValues())) {
			throw new CM_Exception_FormFieldValidation('Invalid value');
		}
		return $userInput;
	}

	public function prepare(array $params) {
		if (!isset($params['display'])) {
			$params['display'] = self::DISPLAY_SELECT;
		}
		if ($params['display'] !== self::DISPLAY_SELECT && $params['display'] !== self::DISPLAY_RADIOS) {
			throw new CM_Exception_InvalidParam('Display needs to be either `select` or `radios`');
		}
		$this->setTplParam('display', $params['display']);
		$this->setTplParam('class', !empty($params['class']) ? $params['class'] : null);

		$this->setTplParam('placeholder', !empty($params['placeholder']));
		$this->setTplParam('optionList', $this->_getOptionList());

		$this->setTplParam('translate', !empty($params['translate']) || !empty($params['translatePrefix']));
		$this->setTplParam('translatePrefix', !empty($params['translatePrefix']) ? $params['translatePrefix'] : '');
		$this->setTplParam('colSize', !empty($params['colSize']) ? $params['colSize'] : '');
	}
}
