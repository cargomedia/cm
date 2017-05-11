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

    /**
     * @return string[]
     */
    public function getErrors() {
        return $this->errors;
    }

    protected function _processView(array $output) {
        $success = array();
        $form = $this->_getView();
        $className = get_class($form);
        if (!$form instanceof CM_Form_Abstract) {
            throw new CM_Exception_Invalid('ClassName is not `CM_Form_Abstract` instance', null, ['className' => $className]);
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
        return $output;
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('form')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}

