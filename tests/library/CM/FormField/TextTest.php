<?php

class CM_FormField_TextTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRender() {
        $form = $this->getMockForm();
        $field = new CM_FormField_Text();
        $fieldName = 'foo';
        $doc = $this->_renderFormField($form, $field, $fieldName);
        $this->assertSame(1, $doc->getCount('input'));
        $this->assertSame(
            '<div class="CM_FormField_Text CM_FormField_Abstract CM_View_Abstract" id="' . $form->getAutoId() . '-foo"><input name="foo" id="' .
            $form->getTagAutoId($fieldName . '-input') .
            '" type="text" class="textinput " /><span class="messages"></span></div>', $doc->getHtml());
    }

    public function testRenderValue() {
        $form = $this->getMockForm();
        $field = new CM_FormField_Text();
        $field->setValue('bar');
        $fieldName = 'foo';
        $doc = $this->_renderFormField($form, $field, $fieldName);
        $this->assertSame('bar', $doc->getAttr('input', 'value'));
        $this->assertSame(
            '<div class="CM_FormField_Text CM_FormField_Abstract CM_View_Abstract" id="' . $form->getAutoId() . '-foo"><input name="foo" id="' .
            $form->getTagAutoId($fieldName . '-input') .
            '" type="text" value="bar" class="textinput " /><span class="messages"></span></div>', $doc->getHtml());
    }

    public function testValidateMinLength() {
        $field = new CM_FormField_Text(['lengthMin' => 3]);
        $response = $this->getMockBuilder('CM_Response_View_Form')->disableOriginalConstructor()->getMockForAbstractClass();
        $render = new CM_Render();
        try {
            $field->validate('foo', $response);
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value to be long enough');
        }
        try {
            $field->validate('fo', $response);
            $this->fail('Expected value to be too short');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too short', $ex->getMessagePublic($render));
        }
        try {
            // this string is 3 bytes long
            $field->validate('fó', $response);
            $this->fail('Expected value to be too short');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too short', $ex->getMessagePublic($render));
        }
    }

    public function testValidateMaxLength() {
        $field = new CM_FormField_Text(['lengthMax' => 3]);
        $response = $this->getMockBuilder('CM_Response_View_Form')->disableOriginalConstructor()->getMockForAbstractClass();
        $render = new CM_Render();
        try {
            $field->validate('foo', $response);
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be too long');
        }
        try {
            $field->validate('fooo', $response);
            $this->fail('Expected value to be too long');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too long', $ex->getMessagePublic($render));
        }
        try {
            // this string is actually 5 bytes long
            $field->validate('fóó', $response);
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be too long');
        }
    }

    public function testValidateBadwords() {
        $badwordsList = new CM_Paging_ContentList_Badwords();
        $field = new CM_FormField_Text(['forbidBadwords' => true]);
        $response = $this->getMockBuilder('CM_Response_View_Form')->disableOriginalConstructor()->getMockForAbstractClass();
        $render = new CM_Render();
        try {
            $field->validate('foo', $response);
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be a badword');
        }
        $badwordsList->add('foo');
        try {
            $field->validate('foo', $response);
            $this->fail('Expected value to be a badword');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('The word `foo` is not allowed', $ex->getMessagePublic($render));
        }

        $field = new CM_FormField_Text(null, null, false);
        try {
            $field->validate('foo', $response);
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Badword-validation shouldn\'t be activated');
        }
    }

    function testArrayInputInvalidCharsRemoval() {
        $invalidInputs = array(chr(240), chr(192), chr(200) . chr(210), 'something' . chr(244));
        $field = new CM_FormField_Text();
        foreach ($invalidInputs as $input) {
            $filtered = $field->filterInput($input);
            $this->assertNotSame($filtered, $input);
        }
    }
}
