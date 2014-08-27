<?php

class CM_FormField_FloatTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Float(['name' => 'foo']);
        $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $parsedInput = $field->parseUserInput(1.3);
        $this->assertSame(1.3, $parsedInput);
        $field->validate($environment, $parsedInput);
        $field->validate($environment, 'foo');
    }
}
