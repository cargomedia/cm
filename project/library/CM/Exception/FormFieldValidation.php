<?php

class CM_Exception_FormFieldValidation extends CM_Exception {

	/**
	 * @param string $message
	 */
	public function __construct($message) {
		parent::__construct($message, true);
	}
}
