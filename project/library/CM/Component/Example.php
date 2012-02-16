<?php

class CM_Component_Example extends CM_Component_Abstract {

	public function prepare() {
		$foo = $this->_params->getString('foo', 'value1');

		$this->_js->uname = `uname`;
		$this->setTplParam('now', time());
		$this->setTplParam('foo', $foo);

	}

	public function checkAccessible() {
	}

	public static function ajax_test(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_Component_Ajax $response) {
		$x = $params->getString('x');
		//$response->reloadComponent();
		return 'x=' . $x;
	}

	public static function ajax_error(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_Component_Ajax $response) {
		$status = $params->getInt('status', 200);
		$text = $params->has('text') ? $params->getString('text') : null;
		if (in_array($status, array(500, 599))) {
			$response->addHeaderRaw('HTTP/1.1 ' . $status . ' Internal Server Error');
			$response->sendHeaders();
			exit($text);
		}
		$exception = $params->getString('exception');
		if ($exception != 'CM_Exception' && $exception != 'CM_Exception_AuthRequired') {
			$exception = 'CM_Exception';
		}
		$public = $params->getBoolean('public', false);
		throw new $exception($text, $public);
	}

	public static function ajax_ping(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_Component_Ajax $response) {
		$number = $params->getInt('number');
		self::stream($response->getViewer(true), 'ping', array("number" => $number, "message" => 'pong'));
	}

	public static function rpc_time() {
		return time();
	}
}
