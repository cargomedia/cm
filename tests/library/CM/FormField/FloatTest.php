<?php

class CM_FormField_FloatTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $field = new CM_FormField_Float(['name' => 'foo']);
        $response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);
        /** @var CM_Response_Abstract $response */

        $validationResult = $field->validate($environment, 1.3, $response);
        $this->assertSame(1.3, $validationResult);
        $field->validate($environment, 'foo', $response);
    }
}
