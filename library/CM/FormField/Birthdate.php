<?php

class CM_FormField_Birthdate extends CM_FormField_Date {

	/** @var integer */
	protected $_minAge;

	/** @var integer */
	protected $_maxAge;

	/**
	 * @param int $minAge
	 * @param int $maxAge
	 */
	public function __construct($minAge, $maxAge) {
		$this->_minAge = (int) $minAge;
		$this->_maxAge = (int) $maxAge;

		$yearFirst = date('Y') - $this->_minAge;
		$yearLast = date('Y') - $this->_maxAge;
		parent::__construct($yearFirst, $yearLast);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = parent::validate($userInput, $response);
		try {
			$age = $userInput->diff(new DateTime())->y;
		} catch (Exception $e) {
			throw new CM_Exception_FormFieldValidation('Invalid age');
		}
		if ($age < $this->_minAge || $age > $this->_maxAge) {
			throw new CM_Exception_FormFieldValidation('Invalid age');
		}
		return $userInput;
	}
}
