<?php

class CM_Response_RPC extends CM_Response_Abstract {
	
	/**
	 * @return string json encoded string
	 */
	public function process() {
		$this->setHeader('Content-Type', 'application/json');

		$output = array();
		try {
			$query = $this->_request->getQuery();
			if (!isset($query['method']) || 1 != substr_count($query['method'], '.') || !preg_match('/^[\w_\.]+$/i', $query['method'])) {
				throw new CM_Exception_Invalid('Illegal method: `' . $query['method'] . '`');
			}
			if (!isset($query['params']) || !is_array($query['params'])) {
				throw new CM_Exception_Invalid('Illegal params');
			}
			$params = $query['params'];
			list($class, $function) = explode('.', $query['method']);
			$output['success'] = array('result' => call_user_func_array(array($class, 'rpc_' . $function), $params),);
		} catch (CM_Exception_AuthRequired $e) {
			$error = array('type' => get_class($e), 'msg' => $e->getMessagePublic(), 'isPublic' => $e->isPublic());
		}
		if (isset($error)) {
			$output['error'] = $error;
		}
		return json_encode($output);
	}
}
