<?php

class CM_FormField_SliderTest extends CMTest_TestCase {

    public function testValidate() {
        /** @var CM_Frontend_Environment $environment */
        $environment = $this->mockClass('CM_Frontend_Environment')->newInstanceWithoutConstructor();

        $min = 0;
        $max = 10;
        $step = 0.5;
        $field = new CM_FormField_Slider(['name' => 'slider', 'min' => $min, 'max' => $max, 'step' => $step]);
        
        $this->assertSame(3.5, $field->validate($environment, 3.5));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 15);
        }));
        
        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 3.7);
        }));
    }
}
