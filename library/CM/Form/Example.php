<?php

class CM_Form_Example extends CM_Form_Abstract {

    public function setup() {
        $this->registerField('text', new CM_FormField_Text(['name' => 'text']));
        $this->registerField('int', new CM_FormField_Integer(['name' => 'int', 'min' => -10, 'max' => 20, 'step' => 2]));
        $this->registerField('locationSlider', new CM_FormField_Distance(['name' => 'locationSlider']));
        $this->registerField('location', new CM_FormField_Location(['name' => 'location', 'fieldNameDistance' => 'locationSlider']));
        $this->registerField('file', new CM_FormField_File(['name' => 'file', 'cardinality' => 2]));
        $this->registerField('image', new CM_FormField_FileImage(['name' => 'image', 'cardinality' => 2]));
        $this->registerField('color', new CM_FormField_Color(['name' => 'color']));
        $this->registerField('date', new CM_FormField_Date(['name' => 'date']));
        $this->registerField('set', new CM_FormField_Set(['name' => 'set', 'values' => array(1 => 'Eins', 2 => 'Zwei'), 'labelsInValues' => true]));
        $this->registerField('boolean', new CM_FormField_Boolean(['name' => 'boolean']));
        $this->registerField('setSelect1', new CM_FormField_Set_Select(['name' => 'setSelect1', 'values' => array(1 => 'Eins', 2 => 'Zwei'), 'labelsInValues' => true]));
        $this->registerField('setSelect2', new CM_FormField_Set_Select(['name' => 'setSelect2', 'values' => array(1 => 'Eins', 2 => 'Zwei'), 'labelsInValues' => true]));
        $this->registerField('setSelect3', new CM_FormField_Set_Select(['name' => 'setSelect3', 'values' => array(1 => 'Female', 2 => 'Male'), 'labelsInValues' => true]));

        $this->registerAction(new CM_FormAction_Example_Go($this));
    }

    public function _renderStart(CM_Params $params) {
        $ip = CM_Request_Abstract::getInstance()->getIp();
        if ($locationGuess = CM_Model_Location::findByIp($ip)) {
            $this->getField('location')->setValue($locationGuess);
        }
    }
}
