<?php

class CM_FormField_SuggestTest extends CMTest_TestCase
{
    public function testParseUserInput()
    {
        $field = $this->getMockForAbstractClass('CM_FormField_Suggest');

        $parsedInput = $field->parseUserInput('foo');
        $this->assertSame(array('foo'), $parsedInput);

        $parsedInput = $field->parseUserInput('foo,bar');
        $this->assertSame(array('foo', 'bar'), $parsedInput);

        $parsedInput = $field->parseUserInput('foo,bar,foo');
        $this->assertCount(2, $parsedInput);
        $this->assertContains('foo', $parsedInput);
        $this->assertContains('bar', $parsedInput);
    }

    public function testValidate()
    {
        $environment = new CM_Frontend_Environment();
        $field = $this->getMockForAbstractClass('CM_FormField_Suggest');
        $field->validate($environment, array('foo', 'bar'));

        $field = $this->getMockForAbstractClass('CM_FormField_Suggest', array(array('cardinality' => 2)));
        $field->validate($environment, array('foo', 'bar'));
    }

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidateTooMany()
    {
        $environment = new CM_Frontend_Environment();
        $field = $this->getMockForAbstractClass('CM_FormField_Suggest', array(array('cardinality' => 2)));
        $field->validate($environment, array('foo', 'bar', 'zar'));
    }
}
