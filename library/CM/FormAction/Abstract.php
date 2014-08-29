<?php

abstract class CM_FormAction_Abstract {

    /** @var string */
    private $_name;

    /** @var CM_Form_Abstract */
    private $_form;

    /** @var CM_FormField_Abstract[]|null */
    private $_fieldList = null;

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
     * @return array [string => bool]
     */
    public function getFieldList() {
        if (null === $this->_fieldList) {
            $this->_fieldList = array();
            foreach ($this->_form->getFields() as $fieldName => $field) {
                $this->_fieldList[$fieldName] = in_array($fieldName, $this->_getRequiredFields());
            }
        }
        return $this->_fieldList;
    }

    /**
     * @return CM_Form_Abstract
     */
    public function getForm() {
        return $this->_form;
    }

    /**
     * @return string
     */
    final public function js_presentation() {
        $data = array();
        $data['fields'] = (object) $this->getFieldList();

        return json_encode($data);
    }

    /**
     * @param array                 $data
     * @param CM_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     */
    final public function checkData(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
        $this->_checkData(CM_Params::factory($data, false), $response, $form);
    }

    /**
     * @param array                 $data
     * @param CM_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     * @return mixed
     */
    final public function process(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
        return $this->_process(CM_Params::factory($data, false), $response, $form);
    }

    /**
     * @return string[]
     */
    protected function _getRequiredFields() {
        return array();
    }

    /**
     * @param CM_Params             $params
     * @param CM_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     */
    protected function _checkData(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
    }

    /**
     * @param CM_Params             $params
     * @param CM_Response_View_Form $response
     * @param CM_Form_Abstract      $form
     * @return mixed
     */
    protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
    }
}
