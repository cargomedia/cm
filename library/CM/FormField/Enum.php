<?php

class CM_FormField_Enum extends CM_FormField_Set_Select {

    /**
     * @throws CM_Exception_Invalid
     */
    protected function _initialize() {
        $enumClassName = $this->_params->get('className');
        if (!is_a($enumClassName, 'CM_Type_Enum', true)) {
            throw new CM_Exception_Invalid('Invalid "className" parameter');
        }
        $this->_params->set('values', $enumClassName::getConstantList());
        parent::_initialize();
    }
}
