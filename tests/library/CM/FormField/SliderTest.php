<?php

class CM_FormField_SliderTest extends CMTest_TestCase {

    public function testValidate() {
        $environment = new CM_Frontend_Environment();

        $field = new CM_FormField_Slider(['name' => 'slider', 'min' => 0, 'max' => 10, 'step' => 0.5]);

        $this->assertSame(3.5, $field->validate($environment, 3.5));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function () use ($field, $environment) {
            $field->validate($environment, 15);
        }));
    }

}
