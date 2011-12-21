<?php

class CM_FormField_Distance extends CM_FormField_Integer {
	
	public function validate($userInput) {
		return parent::validate($userInput) * 1609;
	}
	
	/**
	 * @return int External Value
	 */
	public function getValue() {
		return parent::getValue() / 1609;
	}
	
}
