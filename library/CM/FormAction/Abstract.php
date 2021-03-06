<?php

abstract class CM_FormAction_Abstract {

    /** @var string */
    private $_name;

    /** @var CM_Form_Abstract */
    private $_form;

    /**
     * @param CM_Form_Abstract $form
     * @throws CM_Exception
     */
    public function __construct(CM_Form_Abstract $form) {
        $this->_form = $form;
        if (!preg_match('/^\w+_FormAction_[^_]+_(.+)$/', get_class($this), $matches)) {
            throw new CM_Exception("Cannot detect action name from form action class name");
        }
        $this->_name = $matches[1];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return CM_Form_Abstract
     */
    public function getForm() {
        return $this->_form;
    }

    /**
     * @param array                 $data
     * @param CM_Http_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     */
    final public function checkData(array $data, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
        $this->_checkData(CM_Params::factory($data, false), $response, $form);
    }

    /**
     * @param array                 $data
     * @param CM_Http_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     * @return mixed
     */
    final public function process(array $data, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
        return $this->_process(CM_Params::factory($data, false), $response, $form);
    }

    /**
     * @param CM_Params             $params
     * @param CM_Http_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     */
    protected function _checkData(CM_Params $params, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
    }

    /**
     * @param CM_Params             $params
     * @param CM_Http_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     * @return mixed
     */
    protected function _process(CM_Params $params, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
    }
}
