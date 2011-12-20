<?php

class CM_FormFieldValidationException extends Exception {
	private $error_key;

	/**
	 * Constructor.
	 *
	 * @param string $error_key
	 */
	public function __construct($error_key) {
		$this->error_key = $error_key;

		//parent::__construct('', 0);
		}

	/**
	 * Get validation error key.
	 *
	 * @return string
	 */
	public function getErrorKey() {
		return $this->error_key;
	}

}
