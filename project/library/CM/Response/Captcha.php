<?php

class CM_Response_Captcha extends CM_Response_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'image/png');
		$params = $this->_request->getQuery();
		$captcha = new CM_Captcha($params['id']);
		return $captcha->render(200, 40);
	}
}
