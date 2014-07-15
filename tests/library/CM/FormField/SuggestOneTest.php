<?php

class CM_FormField_SuggestOneTest extends CMTest_TestCase {

    public function testParseUserInput() {
        $field = $this->getMockForAbstractClass('CM_FormField_SuggestOne');

        $parsedInput = $field->parseUserInput('foo');
        $this->assertSame('foo', $parsedInput);

        $parsedInput = $field->parseUserInput(null);
        $this->assertSame('', $parsedInput);
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testTooMany() {
        $field = $this->getMockForAbstractClass('CM_FormField_SuggestOne');
        $parsedInput = $field->parseUserInput('foo,bar');
        $this->assertSame('foo', $parsedInput);
    }
}
