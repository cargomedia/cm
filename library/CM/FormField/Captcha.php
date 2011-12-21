<?php

class CM_FormField_Captcha extends CM_FormField_Abstract {

	public function __construct($name = 'captcha') {
		parent::__construct($name);
		$this->_options['urlImage'] = SITE_URL . 'captcha/' . CM_Render::getInstance()->getSite()->getId() . '/';
	}
	
	public function render(array $params, CM_Form_Abstract $form) {
		$this->setTplParam('imageId', self::rpc_createNumber());
	}

	public function validate($userInput) {
		$id = (int) $userInput['image_id'];
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
