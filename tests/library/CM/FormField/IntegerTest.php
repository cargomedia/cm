<?php

class CM_FormField_IntegerTest extends CMTest_TestCase {

    public function testValidate() {
        /** @var CM_Frontend_Environment $environment */
        $environment = $this->mockClass('CM_Frontend_Environment')->newInstanceWithoutConstructor();
        
        $field = new CM_FormField_Integer(['name' => 'int']);
        $this->assertSame(1, $field->validate($environment, 1));
        
        $exception = $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 'foo');
        });
        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $exception);
    }


    public function testInitializeMinMaxOptions() {
        $this->assertInstanceOf('CM_Exception_InvalidParam', $this->catchException(function() {
            new CM_FormField_Integer(['name' => 'foo', 'min' => 0.1]);
        }));

        $this->assertInstanceOf('CM_Exception_InvalidParam', $this->catchException(function() {
            new CM_FormField_Integer(['name' => 'foo', 'max' => 1.2]);
        }));
    }

    public function testValidateMinMaxOptions() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Integer(['name' => 'foo', 'min' => -2, 'max' => 2]);

        $this->assertSame(-1, $field->validate($environment, -1));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, -3);
        }));

        $this->assertInstanceOf('CM_Exception_FormFieldValidation', $this->catchException(function() use ($field, $environment) {
            $field->validate($environment, 4);
        }));
    }
}
