<?php

class CM_FormField_Date extends CM_FormField_Abstract {

	/** @var int */
	protected $_yearMin;

	/** @var int */
	protected $_yearMax;

	/**
	 * @param string     $field_name
	 * @param int|null   $yearMin
	 * @param int|null   $yearMax
	 */
	public function __construct($field_name, $yearMin = null, $yearMax = null) {
		parent::__construct($field_name);
		if (null === $yearMin) {
			$yearMin = date('Y') - 100;
		}
		$this->_yearMin = (int) $yearMin;

		if (null === $yearMax) {
			$yearMax = date('Y');
		}
		$this->_yearMax = (int) $yearMax;
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		if (empty($userInput['day']) || empty($userInput['month']) || empty($userInput['year'])) {
			throw new CM_Exception_FormFieldValidation("day, month or year not set");
		}
		$dd = trim($userInput['day']);
		$mm = trim($userInput['month']);
		$yy = trim($userInput['year']);

		return new DateTime($yy . '-' . $mm . '-' . $dd);
	}

	public function prepare(array $params) {
		$this->setTplParam('class', isset($params['class']) ? $params['class'] : null);

		$value = $this->getValue();
		$this->setTplParam('yy', $value ? $value->format('Y') : null);
		$this->setTplParam('mm', $value ? $value->format('m') : null);
		$this->setTplParam('dd', $value ? $value->format('d') : null);

		$this->setTplParam('minYear', $this->_yearMin);
		$this->setTplParam('maxYear', $this->_yearMax);
	}

	public function isEmpty($userInput) {
		return empty($userInput['day']) && empty($userInput['month']) && empty($userInput['year']);
	}
}
