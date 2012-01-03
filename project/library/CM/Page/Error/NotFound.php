<?php
class CM_Page_Error_NotFound extends CM_Page_Abstract {

	public function prepare(CM_Response_Abstract $response) {
		$response->setHeaderNotfound();
		$this->setTitle('Page Not Found');
	}
}