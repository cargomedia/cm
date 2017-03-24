<?php

class CM_FormField_TextTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRender() {
        $field = new CM_FormField_Text(['name' => 'foo']);
        $field->setValue('bar');

        $render = new CM_Frontend_Render();
        $doc = $this->_renderFormField($field, null, $render);
        /** @var CM_Frontend_Render $render */
        $autoId = $render->getGlobalResponse()->getTreeRoot()->getValue()->getAutoId();

        $this->assertSame($autoId, $doc->getAttribute('id'));
        $this->assertSame(1, $doc->find('input[name="foo"]')->count());
        $this->assertSame('bar', $doc->find('input[name="foo"]')->getAttribute('value'));
    }

    public function testValidateMinLength() {
        $field = new CM_FormField_Text(['name' => 'foo', 'lengthMin' => 3]);
        $environment = new CM_Frontend_Environment();
        $render = new CM_Frontend_Render();
        try {
            $field->validate($environment, 'foo');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value to be long enough');
        }
        try {
            $field->validate($environment, 'fo');
            $this->fail('Expected value to be too short');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too short', $ex->getMessagePublic($render));
        }
        try {
            // this string is 3 bytes long
            $field->validate($environment, 'fó');
            $this->fail('Expected value to be too short');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too short', $ex->getMessagePublic($render));
        }
    }

    public function testValidateMaxLength() {
        $field = new CM_FormField_Text(['name' => 'foo', 'lengthMax' => 3]);
        $environment = new CM_Frontend_Environment();
        $render = new CM_Frontend_Render();
        try {
            $field->validate($environment, 'foo');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be too long');
        }
        try {
            $field->validate($environment, 'fooo');
            $this->fail('Expected value to be too long');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('Too long', $ex->getMessagePublic($render));
        }
        try {
            // this string is actually 5 bytes long
            $field->validate($environment, 'fóó');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be too long');
        }
    }

    public function testValidateBadwords() {
        $badwordsList = new CM_Paging_ContentList_Badwords();
        $field = new CM_FormField_Text(['name' => 'foo', 'forbidBadwords' => true]);
        $environment = new CM_Frontend_Environment();
        $render = new CM_Frontend_Render();
        try {
            $field->validate($environment, 'foo');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Expected value not to be a badword');
        }
        $badwordsList->add('foo');
        try {
            $field->validate($environment, 'foo');
            $this->fail('Expected value to be a badword');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->assertContains('The word `foo` is not allowed', $ex->getMessagePublic($render));
        }

        $field = new CM_FormField_Text(['name' => 'foo', 'forbidBadwords' => false]);
        try {
            $field->validate($environment, 'foo');
        } catch (CM_Exception_FormFieldValidation $ex) {
            $this->fail('Badword-validation shouldn\'t be activated');
        }
    }

    function testArrayInputInvalidCharsRemoval() {
        $invalidInputs = array(chr(240), chr(192), chr(200) . chr(210), 'something' . chr(244));
        $field = new CM_FormField_Text(['name' => 'foo']);
        foreach ($invalidInputs as $input) {
            $filtered = $field->filterInput($input);
            $this->assertNotSame($filtered, $input);
        }
    }
}
