<?php

class CM_Form_Example extends CM_Form_Abstract {

    protected function _initialize() {
        $this->registerField(new CM_FormField_Text(['name' => 'text']));
        $this->registerField(new CM_FormField_Email(['name' => 'email']));
        $this->registerField(new CM_FormField_Password(['name' => 'password']));
        $this->registerField(new CM_FormField_Textarea(['name' => 'textarea']));
        $this->registerField(new CM_FormField_Float(['name' => 'float']));
        $this->registerField(new CM_FormField_Money(['name' => 'money']));
        $this->registerField(new CM_FormField_Url(['name' => 'url']));
        $this->registerField(new CM_FormField_Integer(['name' => 'int']));
        $this->registerField(new CM_FormField_Slider(['name' => 'slider', 'min' => 0, 'max' => 2.5, 'step' => 0.1]));
        $this->registerField(new CM_FormField_SliderRange(['name' => 'sliderRange', 'min' => 0, 'max' => 2.5, 'step' => 0.1]));
        $this->registerField(new CM_FormField_Distance(['name' => 'locationSlider']));
        $this->registerField(new CM_FormField_Location(['name' => 'location', 'fieldNameDistance' => 'locationSlider']));
        $this->registerField(new CM_FormField_File(['name' => 'file', 'cardinality' => 2]));
        $this->registerField(new CM_FormField_FileImage(['name' => 'image', 'cardinality' => 2]));
        $this->registerField(new CM_FormField_Color(['name' => 'color']));
        $this->registerField(new CM_FormField_ColorPalette(['name' => 'color2', 'palette' => [
            CM_Color_RGB::fromHexString('ff0000'),
            CM_Color_RGB::fromHexString('00ff00'),
            CM_Color_RGB::fromHexString('0000ff'),
        ]]));
        $this->registerField(new CM_FormField_Date(['name' => 'date']));
        $this->registerField(new CM_FormField_DateTimeInterval(['name' => 'dateTimeInterval', 'yearFirst' => date('Y'), 'yearLast' => (int) date('Y') + 1]));
        $this->registerField(new CM_FormField_Birthdate(['name' => 'birthdate', 'minAge' => 18, 'maxAge' => 30]));
        $this->registerField(new CM_FormField_GeoPoint(['name' => 'geopoint']));
        $this->registerField(new CM_FormField_Set(['name' => 'set', 'values' => array(1 => 'Eins', 2 => 'Zwei'), 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Boolean(['name' => 'boolean']));
        $this->registerField(new CM_FormField_Boolean(['name' => 'booleanSwitch']));
        $this->registerField(new CM_FormField_Boolean(['name' => 'booleanButton']));
        $this->registerField(new CM_FormField_Boolean(['name' => 'booleanButton2']));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect1', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect2', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect3', 'values' => [1 => 'Foo', 2 => 'Bar'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_TreeSelect(['name' => 'treeselect', 'tree' => CM_Model_LanguageKey::getTree()]));
        $this->registerField(new CM_FormField_Geometry_Vector2(['name' => 'vector2']));
        $this->registerField(new CM_FormField_Geometry_Vector3(['name' => 'vector3']));
        $this->registerField(new CM_FormField_Captcha(['name' => 'captcha']));

        $this->registerAction(new CM_FormAction_Example_Submit($this));
    }

    protected function _getRequiredFields() {
        return ['text', 'money'];
    }

    public function ajax_validate(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $data = $params->getArray('data');
        $result = [];
        foreach ($data as $name => $userInput) {
            $field = $this->getField($name);
            $empty = $field->isEmpty($userInput);
            $value = null;
            $validationError = null;
            if (!$empty) {
                try {
                    $value = $field->validate($response->getEnvironment(), $userInput);
                } catch (CM_Exception_FormFieldValidation $e) {
                    $validationError = $e->getMessagePublic($response->getRender());
                }
            }
            $result[$name] = [
                'value'           => $value,
                'empty'           => $empty,
                'validationError' => $validationError,
            ];
        }
        return $result;
    }
}
