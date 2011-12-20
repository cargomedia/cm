<?php

abstract class CM_FormField_SuggestOne extends CM_FormField_Suggest {

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		parent::__construct($name, 1);
	}

	public function setValue($value) {
		$value = $value ? array($value) : null;
		parent::setValue($value);
	}
	
	/**
	 * @param string $userInput
	 * @return string|null
	 */
	public function validate($userInput) {
		$values = parent::validate($userInput);
		return $values ? reset($values) : null;
	}
}
