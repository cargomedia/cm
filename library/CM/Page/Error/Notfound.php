<?php
class CM_Page_Error_NotFound extends CM_Page_Abstract {

	public function prepare(CM_RequestHandler_Abstract $requestHandler) {
		$requestHandler->setHeaderNotfound();
		$this->setTitle('Page Not Found');
	}
}