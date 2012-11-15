<?php

class CM_Response_RPC extends CM_Response_Abstract {

	protected function _process() {
		$output = array();
		try {
			$query = $this->_request->getQuery();
			if (!isset($query['method']) || 1 != substr_count($query['method'], '.') || !preg_match('/^[\w_\.]+$/i', $query['method'])) {
				throw new CM_Exception_Invalid('Illegal method: `' . $query['method'] . '`', null, null, CM_Exception::WARN);
			}
			if (!isset($query['params']) || !is_array($query['params'])) {
				throw new CM_Exception_Invalid('Illegal params', null, null, CM_Exception::WARN);
			}
			$params = $query['params'];
			list($class, $function) = explode('.', $query['method']);
			$output['success'] = array('result' => call_user_func_array(array($class, 'rpc_' . $function), $params));
		} catch (CM_Exception $e) {
			if (!($e->isPublic() || in_array(get_class($e), self::_getConfig()->catch))) {
				throw $e;
			}
			$output['error'] = array('type' => get_class($e), 'msg' => $e->getMessagePublic($this->getRender()), 'isPublic' => $e->isPublic());
		}

		$this->setHeader('Content-Type', 'application/json');
		$this->_setContent(json_encode($output));
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'rpc';
	}
}
