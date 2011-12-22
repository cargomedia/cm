<?php

class CM_FormField_Captcha extends CM_FormField_Abstract {

	public function prepare(array $params, CM_Form_Abstract $form) {
		$this->setTplParam('imageId', self::rpc_createNumber());
	}

	public function validate($userInput) {
		$id = (int) $userInput['id'];
		$text = (string) $userInput['value'];

		try {
			$captcha = new CM_Captcha($id);
		} catch (CM_Exception_Nonexistent $e) {
			throw new CM_FormFieldValidationException('Invalid captcha reference');
		}
		if (!$captcha->check($text)) {
			throw new CM_FormFieldValidationException('Number doesn\'t match captcha');
		}

		return $userInput;
	}

	/**
	 * @return int Captcha-id
	 */
	public static function rpc_createNumber() {
		return CM_Captcha::create()->getId();
	}
}
