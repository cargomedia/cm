<?php

class CM_FormField_Distance extends CM_FormField_Integer {
	
	function validate($userInput, CM_Response_Abstract $response) {
		return parent::validate($userInput, $response) * 1609;
	}
	
	/**
	 * @return int External Value
	 */
	public function getValue() {
		return parent::getValue() / 1609;
	}
	
}
