<?php
class CM_Page_Error_Notfound extends CM_Page_Abstract {

	public function prepare(CM_RequestHandler_Abstract $requestHandler) {
		$requestHandler->setHeaderNotfound();
		$this->setTitle('Page Not Found');
	}
}