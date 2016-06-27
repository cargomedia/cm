<?php

class CM_FormField_FloatTest extends CMTest_TestCase {

    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Float(['name' => 'foo']);

        $this->assertSame(1.3, $field->validate($environment, 1.3));
        
        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 'foo');
        }));
    }

    public function testValidateMinMaxOptions() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Float(['name' => 'foo', 'min' => 1.3, 'max' => 2.5]);

        $this->assertSame(1.3, $field->validate($environment, 1.3));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 1.2);
        }));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 2.6);
        }));
    }
}
