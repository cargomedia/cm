<?php

class CM_Response_Component_Ajax extends CM_Response_Component_Abstract {

	public function process() {
		$this->setHeader('Content-Type', 'application/json');

		$output = array();
		try {
			$success = array();
			$query = $this->_request->getQuery();
			$functionName = $query['functionName'];
			if (substr($functionName, 0, 5) != 'ajax_') {
				throw new CM_Exception_Invalid('Invalid function name `' . $functionName . '`.');
			}
			$functionName = array($this->_component['className'], $query['functionName']);
			$componentHandler = new CM_ComponentFrontendHandler();
			$functionParams = CM_Params::factory($query['params']);
			$success['data'] = CM_Params::encode(call_user_func($functionName, $functionParams, $componentHandler, $this));
	
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
			$output['error'] = array('type' => get_class($e), 'msg' => $e->getMessagePublic());
		}
		return json_encode($output);
	}

}
