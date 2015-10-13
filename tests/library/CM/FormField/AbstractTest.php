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

    public function testIsEmpty() {
        /** @var CM_FormField_Abstract|\Mocka\AbstractClassTrait $field */
        $field = $this->mockObject('CM_FormField_Abstract');

        $this->assertSame(true, $field->isEmpty(null));

        $this->assertSame(true, $field->isEmpty(''));
        $this->assertSame(false, $field->isEmpty('mega'));

        $this->assertSame(false, $field->isEmpty(0));
        $this->assertSame(false, $field->isEmpty(1));

        $this->assertSame(false, $field->isEmpty(true));
        $this->assertSame(false, $field->isEmpty(false));

        $this->assertSame(true, $field->isEmpty([]));
        $this->assertSame(false, $field->isEmpty([1]));
    }
}
