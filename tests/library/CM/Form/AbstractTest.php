<?php

class CM_Form_AbstractTest extends CMTest_TestCase {

    /** @var int */
    public static $formActionProcessCount = 0;

    /** @var CM_Params|null */
    public static $formActionData = null;

    public function testForm() {
        $form = new CM_Form_MockForm();
        $action = new CM_FormAction_MockForm_TestExampleAction($form);
        $data = array('color' => '#123123', 'must_check' => 'checked', 'text' => 'foo');

        self::$formActionProcessCount = 0;
        $response = $this->getResponseFormAction($action, $data);
        $this->assertSame(1, self::$formActionProcessCount);
        $this->assertFormResponseSuccess($response);
    }

    public function testMissingField() {
        $form = new CM_Form_MockForm();
        $action = new CM_FormAction_MockForm_TestExampleAction($form);
        $data = array('color' => '#123123', 'text' => 'foo');
        $response = $this->getResponseFormAction($action, $data);
        $this->assertFormResponseError($response);
    }

    public function testAllowedMissingField() {
        $form = new CM_Form_MockForm();
        $action = new CM_FormAction_MockForm_TestExampleAction($form);
        $data = array('must_check' => 'checked', 'text' => 'foo');
        $response = $this->getResponseFormAction($action, $data);

        $this->assertFormResponseSuccess($response);
        $this->assertFalse(self::$formActionData->has('color'));
    }

    public function testProcessInvalidCharsRequired() {
        $invalidChars = array(chr(192), chr(214), chr(255), chr(140));
        foreach ($invalidChars as $inputChar) {

            $form = new CM_Form_MockForm();
            $formAction = new CM_FormAction_MockForm_TestExampleAction($form);

            $data = array('must_check' => 'checked', 'text' => $inputChar);
            $response = $this->getResponseFormAction($formAction, $data);

            try {
                $this->assertFormResponseError($response, null, 'text');
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                $this->assertNotSame($inputChar, self::$formActionData->getString('text'));
            }
        }
    }

    public function testValidateValues() {
        $userInputList = array(
            'date'  => array(
                'year'  => 1984,
                'month' => 12,
                'day'   => 29,
            ),
            'color' => 'invalid-color',
        );
        $expected = array(
            'date' => new DateTime('1984-12-29'),
        );
        $form = new CM_Form_MockForm();
        $method = new ReflectionMethod($form, '_validateValues');
        $method->setAccessible(true);
        $validValues = $method->invoke($form, $userInputList, new CM_Frontend_Environment());
        $this->assertEquals($expected, $validValues);
    }

    public function testSetValues() {
        $values = array(
            'text' => 'foo',
        );
        $form = new CM_Form_MockForm();
        $method = new ReflectionMethod($form, '_setValues');
        $method->setAccessible(true);
        $method->invoke($form, $values);
        $this->assertSame('foo', $form->getField('text')->getValue());
        $this->assertNull($form->getField('color')->getValue());
    }

    /**
     * @return array
     */
    private function _getData() {
        return array(
            'action'    => 'TestExampleAction',
            'classname' => 'CM_Form_MockForm',
            'data'      => array('color' => '#123123', 'must_check' => 'checked', 'text' => 'foo'));
    }
}

class CM_Form_MockForm extends CM_Form_Abstract {

    protected function _initialize() {
        $this->registerField(new CM_FormField_Boolean(['name' => 'must_check']));
        $this->registerField(new CM_FormField_Color(['name' => 'color']));
        $this->registerField(new CM_FormField_Text(['name' => 'text']));
        $this->registerField(new CM_FormField_Text(['name' => 'array']));
        $this->registerField(new CM_FormField_Date(['name' => 'date']));
        $this->registerAction(new CM_FormAction_MockForm_TestExampleAction($this));
    }
}

class CM_FormAction_MockForm_TestExampleAction extends CM_FormAction_Abstract {

    protected function _getRequiredFields() {
        return array('must_check', 'text');
    }

    protected function _process(CM_Params $params, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
        CM_Form_AbstractTest::$formActionProcessCount++;
        CM_Form_AbstractTest::$formActionData = $params;
    }
}
