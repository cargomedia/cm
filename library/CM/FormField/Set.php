<?php


class CM_FormField_Set extends CM_FormField_Abstract {

	/** @var array */
	private $_values = array();

	/** @var bool */
	private $_labelsInValues = false;

	/**
	 * @param array|null $values
	 * @param bool|null  $labelsInValues
	 */
	public function __construct(array $values = null, $labelsInValues = null) {
		$this->_values = (array) $values;
		$this->_labelsInValues = (bool) $labelsInValues;
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		foreach ($userInput as $key => $value) {
			if (!in_array($value, $this->_getValues())) {
				unset($userInput[$key]);
			}
		}
		return $userInput;
	}

	public function prepare(array $params) {
		$this->setTplParam('class', !empty($params['class']) ? $params['class'] : null);
		$this->setTplParam('optionList', $this->_getOptionList());
		$this->setTplParam('translate', !empty($params['translate']) || !empty($params['translatePrefix']));
		$this->setTplParam('translatePrefix', !empty($params['translatePrefix']) ? $params['translatePrefix'] : '');
	}

	/**
	 * @return array
	 */
	protected function _getOptionList() {
		if ($this->_labelsInValues || !$this->_values) {
			return $this->_values;
		} else {
			return array_combine($this->_values, $this->_values);
		}
	}

	/**
	 * @return array
	 */
	protected function _getValues() {
		return array_keys($this->_getOptionList());
	}
}
