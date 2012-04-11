<?php

class CM_Exception_FormFieldValidation extends CM_Exception {

	/**
	 * @param string|null $message
	 * @param boolean $public
	 */
	public function __construct($message = null, $public = true) {
		if (is_null($message)) {
			$public = false;
		}
		parent::__construct($message, $public);
	}
}
