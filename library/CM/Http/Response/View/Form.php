<?php

class CM_Http_Response_View_Form extends CM_Http_Response_View_Abstract {

    /**
     * @var array
     */
    private $errors = array();

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @param string $message
     * @param string $fieldName
     */
    public function addError($message, $fieldName = null) {
        if (isset($fieldName)) {
            $this->errors[] = array($message, $fieldName);
        } else {
            $this->errors[] = $message;
        }
    }

    /**
     * @param string $message
     */
    public function addMessage($message) {
        $this->messages[] = $message;
    }

    /**
     * @return bool
     */
    public function hasErrors() {
        return (bool) count($this->errors);
    }

    protected function _process() {
        $output = array();
        try {
            $success = array();
            $form = $this->_getView();
            $className = get_class($form);
            if (!$form instanceof CM_Form_Abstract) {
                throw new CM_Exception_Invalid('`' . $className . '`is not `CM_Form_Abstract` instance');
            }

            $query = $this->_request->getQuery();
            $actionName = (string) $query['actionName'];
            $data = (array) $query['data'];

            $this->_setStringRepresentation($className . '::' . $actionName);
            $success['data'] = CM_Params::encode($form->process($data, $actionName, $this));

            if (!empty($this->errors)) {
                $success['errors'] = $this->errors;
            }

            $jsCode = $this->getRender()->getGlobalResponse()->getJs();
            if (!empty($jsCode)) {
                $success['exec'] = $jsCode;
            }

            if (!empty($this->messages)) {
                $success['messages'] = $this->messages;
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

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'form';
    }
}

