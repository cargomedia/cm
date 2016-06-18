<?php

class CM_FormField_BirthdateTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testValidate() {
        $formField = new CM_FormField_Birthdate(['name' => 'foo', 'minAge' => 18, 'maxAge' => 30]);
        $environment = new CM_Frontend_Environment();
        $value = $formField->validate($environment, array('year' => 1995, 'month' => 1, 'day' => 1));
        $this->assertEquals(new DateTime('1995-01-01'), $value);

        $formField->validate($environment, array('year' => 2005, 'month' => 1, 'day' => 1));
    }
}
