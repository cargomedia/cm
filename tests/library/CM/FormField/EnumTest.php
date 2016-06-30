<?php

class CM_FormField_EnumTest extends CMTest_TestCase {

    public function testInitialize() {
        $field = new CM_FormField_Enum(['name' => 'foo', 'className' => 'FormFieldEnumMock']);
        $this->assertInstanceOf('CM_FormField_Enum', $field);

        $exception = $this->catchException(function () {
            new CM_FormField_Enum(['name' => 'bar', 'className' => 'Bar']);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid "className" parameter', $exception->getMessage());
    }

    public function testGetValues() {
        $field = new CM_FormField_Enum(['name' => 'foo', 'className' => 'FormFieldEnumMock']);
        $this->assertSame(['foo', 'bar'], $field->getValues());
    }
}

class FormFieldEnumMock extends CM_Type_Enum {

    const FOO = 'foo';
    const BAR = 'bar';
}
