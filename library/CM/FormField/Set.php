<?php


class CM_FormField_Set extends CM_FormField_Abstract {
	private $_values = array();
	private $_labelsInValues = false;
	private $_columnSize;

	/**
	 * @param string       $name
	 * @param array|null   $values
	 * @param bool|null    $labelsInValues
	 */
	public function __construct($name, array $values = null, $labelsInValues = null) {
		$this->_values = (array) $values;
		$this->_labelsInValues = (bool) $labelsInValues;
		parent::__construct($name);
	}

	/**
	 * @param string $cssSize
	 * @deprecated
	 */
	public function setColumnSize($cssSize) {
		$this->_columnSize = $cssSize;
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
		$this->setTplParam('colSize', !empty($params['colSize']) ? $params['colSize'] : $this->_columnSize);
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
