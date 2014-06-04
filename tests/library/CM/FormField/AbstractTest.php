<?php

class CM_FormField_AbstractTest extends CMTest_TestCase {

    public function testFactory() {
        $className = 'CM_FormField_File';
        $field = CM_FormField_Abstract::factory($className, ['name' => 'foo']);
        $this->assertInstanceOf('CM_FormField_Abstract', $field);
        $this->assertInstanceOf($className, $field);
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testFactoryInvalid() {
        $className = 'InvalidFormFieldClass';
        CM_FormField_Abstract::factory($className);
    }
}
