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
     * @var CM_ViewFrontendHandler
     */
    private $_frontendHandler;

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
        $this->_frontendHandler->append('this.reset();');
    }

    protected function _process() {
        $output = array();
        try {
            $success = array();
            $query = $this->_request->getQuery();
            $formInfo = $this->_getViewInfo('form');

            $className = (string) $formInfo['className'];
            $actionName = (string) $query['actionName'];
            $data = (array) $query['data'];
            $this->_setStringRepresentation($className . '::' . $actionName);

            $form = CM_Form_Abstract::factory($className);
            $form->setup();
            $success['data'] = CM_Params::encode($form->process($data, $actionName, $this));

            if (!empty($this->errors)) {
                $success['errors'] = $this->errors;
            }
            $this->_frontendHandler->append($this->getRender()->getFrontend()->getJs());
            $success['exec'] = $this->_frontendHandler->compile(null);

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

    public static function match(CM_Request_Abstract $request) {
        return $request->getPathPart(0) === 'form';
    }
}

