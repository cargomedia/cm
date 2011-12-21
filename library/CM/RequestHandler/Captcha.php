<?php

class CM_RequestHandler_Captcha extends CM_RequestHandler_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'image/png');
		$params = $this->_request->getQuery();
		$captcha = new CM_Captcha($params['id']);
		$captcha->render(200, 40);
	}
}
