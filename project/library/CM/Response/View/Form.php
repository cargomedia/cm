<?php

class CM_Response_View_Form extends CM_Response_View_Abstract {
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

	public function process() {
		$this->setHeader('Content-Type', 'application/json');

		$output = array();
		try {
			$success = array();
			$query = $this->_request->getQuery();
			$formInfo = $this->_getViewInfo('form');

			$form = CM_Form_Abstract::factory($formInfo['className']);
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

			$this->exec($this->getRender()->getJs()->getJs());

			if (!empty($this->_jsCode)) {
				$success['exec'] = $this->_jsCode;
			}
	
			if (!empty($this->messages)) {
				$success['messages'] = $this->messages;
			}
			$output['success'] = $success;
		} catch (CM_Exception $e) {
			if (!($e->isPublic() || in_array(get_class($e), self::_getConfig()->catch))) {
				throw $e;
			}
			$output['error'] = array('type' => get_class($e), 'msg' => $e->getMessagePublic(), 'isPublic' => $e->isPublic());
		}
		return json_encode($output);
	}

}

