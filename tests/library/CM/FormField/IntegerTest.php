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
}
