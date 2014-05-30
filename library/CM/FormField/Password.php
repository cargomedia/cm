<?php

class CM_FormField_Password extends CM_FormField_Text {

    public function initialize() {
        $this->_params->set('lengthMin', 4);
        $this->_params->set('lengthMax', 100);
        parent::initialize();
    }
}
