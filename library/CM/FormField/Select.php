<?php

class CM_FormField_Select extends CM_FormField_Abstract {

	private $_valuesSet = array();
	private $_type;
	private $_labelsInValues = false;
	private $_labelPrefix;

	const SELECT = 1;
	const RADIO = 2;
	const RADIO_ITEM = 3;

	/**
	 * @param string  $name
	 * @param integer $type		   OPTIONAL
	 * @param array   $valuesSet	  OPTIONAL
	 * @param string  $labelPrefix	OPTIONAL
	 * @param bool	$labelsInValues OPTIONAL
	 */
	public function __construct($name, $type = self::SELECT, array $valuesSet = array(), $labelPrefix = null, $labelsInValues = false) {
		$this->_type = (int) $type;
		$this->_valuesSet = $valuesSet;
		$this->_labelPrefix = (string) $labelPrefix;
		$this->_labelsInValues = (bool) $labelsInValues;
		parent::__construct($name);
	}

	public function validate($userInput) {
		if ($this->_type == self::RADIO || $this->_type == self::SELECT) {
			if (!in_array($userInput, $this->_getValues())) {
				throw new CM_Exception_FormFieldValidation('Invalid value');
			}
		}
		return $userInput;
	}

	public function prepare(array $params, CM_Form_Abstract $form) {
		$this->setTplParam('type', $this->_type);
		$this->setTplParam('class', isset($params['class']) ? $params['class'] : null);

		if ($this->_type == self::RADIO_ITEM) {
			$this->setTplParam('item', isset($params['item']) ? $params['item'] : null);
		}
		if ($this->_type == self::RADIO) {
			$this->setTplParam('colSize', isset($params['col_size']) ? $params['col_size'] : null);
		}
		if ($this->_type == self::SELECT) {
			$this->setTplParam('invite', !empty($params['invite']));
		}
		if ($this->_type == self::RADIO || $this->_type == self::SELECT) {
			$labelsection = isset($params['labelsection']) ? $params['labelsection'] : '%forms._fields.' . $this->getName();
			$this->setTplParam('valuesAndLabels', $this->_getValuesAndLabels($labelsection));
		}
	}

	private function _getValuesAndLabels($labelsection) {
		if ($this->_labelsInValues) {
			$values = $this->_valuesSet;
		} else {
			$values = array();
			foreach ($this->_valuesSet as $value) {
				$lang_key = $this->_labelPrefix ? $this->_labelPrefix . '_' . $value : $value;
				$values[$value] = CM_Language::text($labelsection . '.' . $lang_key);
			}
		}
		return $values;
	}

	private function _getValues() {
		if ($this->_labelsInValues) {
			return array_keys($this->_valuesSet);
		} else {
			return $this->_valuesSet;
		}
	}
}
