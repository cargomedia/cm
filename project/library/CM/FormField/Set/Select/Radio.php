<?php

class CM_FormField_Set_Select_Radio extends CM_FormField_Set_Select {

	public function validate($userInput, CM_Response_Abstract $response) {
		return $userInput;
	}

	public function prepare(array $params) {
		if (!isset($params['item'])) {
			throw new CM_Exception_InvalidParam('`item` param required');
		}
		$this->setTplParam('itemValue', $params['item']);
	}
}
