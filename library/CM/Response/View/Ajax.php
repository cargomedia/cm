<?php

class CM_Response_View_Ajax extends CM_Response_View_Abstract {

	protected function _process() {
		$output = array();
		try {
			$success = array();
			$query = $this->_request->getQuery();
			if (!isset($query['method'])) {
				throw new CM_Exception_Invalid('No method specified', null, null, CM_Exception::WARN);
			}
			if (!preg_match('/^[\w_]+$/i', $query['method'])) {
				throw new CM_Exception_Invalid('Illegal method: `' . $query['method'] . '`', null, null, CM_Exception::WARN);
			}
			if (!isset($query['params']) || !is_array($query['params'])) {
				throw new CM_Exception_Invalid('Illegal params', null, null, CM_Exception::WARN);
			}
			$viewInfo = $this->_getViewInfo();
			$viewClassName = $viewInfo['className'];
			$viewParams = $viewInfo['params'];
			$functionName = 'ajax_' . $query['method'];
			$functionParams = CM_Params::factory($query['params']);
			$this->_setStringRepresentation($viewClassName . '::' . $functionName);
			$componentHandler = new CM_ComponentFrontendHandler();

			$reflection = new ReflectionClass($viewClassName);
			if ($reflection->getMethod($functionName)->isStatic()) {
				$success['data'] = CM_Params::encode(call_user_func(array($viewClassName, $functionName), $functionParams, $componentHandler, $this));
			} else {
				$viewClass = CM_View_Abstract::factory($viewClassName, $viewParams, $this->getViewer());
				if ($viewClass instanceof CM_View_AccessRestrictable) {
					$viewClass->checkAccessible();
				}
				$success['data'] = $viewClass->$functionName($functionParams, $componentHandler, $this);
			}

			$exec = $componentHandler->compile_js('this');

			CM_Frontend::concat_js($this->getRender()->getJs()->getJs(), $exec);
			if (strlen($exec)) {
				$success['exec'] = $exec;
			}
			$output['success'] = $success;
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
		return $request->getPathPart(0) === 'ajax';
	}
}
