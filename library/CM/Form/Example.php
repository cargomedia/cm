<?php

class CM_Form_Example extends CM_Form_Abstract {

    public function initialize() {
        $this->registerField(new CM_FormField_Text(['name' => 'text']));
        $this->registerField(new CM_FormField_Integer(['name' => 'int', 'min' => -10, 'max' => 20, 'step' => 2]));
        $this->registerField(new CM_FormField_Distance(['name' => 'locationSlider']));
        $this->registerField(new CM_FormField_Location(['name' => 'location', 'fieldNameDistance' => 'locationSlider']));
        $this->registerField(new CM_FormField_File(['name' => 'file', 'cardinality' => 2]));
        $this->registerField(new CM_FormField_FileImage(['name' => 'image', 'cardinality' => 2]));
        $this->registerField(new CM_FormField_Color(['name' => 'color']));
        $this->registerField(new CM_FormField_Date(['name' => 'date']));
        $this->registerField(new CM_FormField_Set(['name' => 'set', 'values' => array(1 => 'Eins', 2 => 'Zwei'), 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Boolean(['name' => 'boolean']));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect1', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect2', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect3', 'values' => [1 => 'Foo', 2 => 'Bar'], 'labelsInValues' => true]));

        $this->registerAction(new CM_FormAction_Example_Go($this));
    }

    public function prepare(CM_Params $renderParams) {
        $ip = CM_Request_Abstract::getInstance()->getIp();
        if ($locationGuess = CM_Model_Location::findByIp($ip)) {
            $this->getField('location')->setValue($locationGuess);
        }
    }
}
