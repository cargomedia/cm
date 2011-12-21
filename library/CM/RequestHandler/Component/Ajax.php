<?php

class CM_RequestHandler_Component_Ajax extends CM_RequestHandler_Component_Abstract {

	/**
	 * Process the response.
	 */
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
		} catch (CM_Exception_AuthRequired $ex) {
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		} catch (CM_Exception_Blocked $ex) {
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		} catch (CM_Exception_ActionLimit $ex) {
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		} catch (SK_Exception_PremiumRequired $ex) {
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		} catch (CM_Exception_Nonexistent $ex) {
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		} catch (CM_Exception $ex) {
			if (!$ex->isPublic()) {
				throw $ex;
			}
			$output['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic());
		}
		return json_encode($output);
	}

}
