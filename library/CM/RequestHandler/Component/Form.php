<?php

class CM_RequestHandler_Component_Form extends CM_RequestHandler_Component_Abstract {
	/**
	 * Added errors.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Added success messages.
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * A javascript code to execute.
	 *
	 * @var string
	 */
	private $_jsCode;

	/**
	 * Adds an error to response.
	 *
	 * @param string $err_msg
	 * @param string $field_name
	 */
	public function addError($err_msg, $field_name = null) {
		if (isset($field_name)) {
			$this->errors[] = array($err_msg, $field_name);
		} else {
			$this->errors[] = $err_msg;
		}
	}

	/**
	 * Add a success message to response.
	 *
	 * @param string $msg_text
	 * @param string $field_name
	 */
	public function addMessage($msg_text) {
		$this->messages[] = $msg_text;
	}

	/**
	 * Check the response for having an errors.
	 *
	 * @return bool
	 */
	public function hasErrors() {
		return (bool) count($this->errors);
	}
	
	public function reset() {
		$this->exec('this.reset();');
	}

	/**
	 * Add a javascript to execute.
	 *
	 * @param string $jsCode
	 */
	public function exec($jsCode) {
		CM_Frontend::concat_js($jsCode, $this->_jsCode);
	}

	/**
	 * Add a reload to the response.
	 */
	public function reloadPage() {
		$this->exec('window.location.reload(true)');
	}

	/**
	 * Process the response.
	 */
	public function process() {
		$this->setHeader('Content-Type', 'application/json');

		$output = array();
		try {
			$success = array();
			$query = $this->_request->getQuery();
	
			$form = CM_Form_Abstract::factory($query['className']);
			$action = (string) $query['actionName'];
			$data = (array) $query['data'];
			
			$form->setup();
	
			$output = array();
			$success['data'] = $form->process($data, $action, $this);
	
			if (!empty($this->errors)) {
				$success['errors'] = $this->errors;
			}
	
			if ($trackingJs = CM_Tracking::getInstance()->getJs()) {
				$this->exec($trackingJs);
			}
			if ($splittestingJs = SK_Splittesting::getInstance()->getJs()) {
				$this->exec($splittestingJs);
			}
	
			CM_Frontend::concat_js($this->getRender()->getJs()->getJs(), $this->_jsCode);
			if (!empty($this->_jsCode)) {
				$success['exec'] = $this->_jsCode;
			}
	
			if (!empty($this->messages)) {
				$success['messages'] = $this->messages;
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

