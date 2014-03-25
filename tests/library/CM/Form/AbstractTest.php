<?php

class CM_Form_AbstractTest extends CMTest_TestCase {

    /** @var int */
    public static $formActionProcessCount = 0;

    /** @var CM_Params|null */
    public static $formActionData = null;

    function testForm() {
        $data = $this->_getData();
        self::$formActionProcessCount = 0;
        $response = $this->getResponseForm($data['classname'], $data['action'], $data['data']);
        $this->assertSame(1, self::$formActionProcessCount);
        $this->assertFormResponseSuccess($response);
    }

    function testMissingField() {
        $data = $this->_getData();
        unset($data['data']['must_check']);
        $response = $this->getResponseForm($data['classname'], $data['action'], $data['data']);
        $this->assertFormResponseError($response);
    }

    function testAllowedMissingField() {
        $data = $this->_getData();
        unset($data['data']['color']);
        $response = $this->getResponseForm($data['classname'], $data['action'], $data['data']);
        $this->assertFormResponseSuccess($response);
        $this->assertFalse(self::$formActionData->has('color'));
    }

    function testProcessInvalidCharsRequired() {
        foreach (array(chr(192), chr(214), chr(255), chr(140)) as $inputChar) {
            $request = $this->getMockBuilder('CM_Request_Post')->setConstructorArgs(array('/form/null'))->setMethods(array('getQuery'))->getMock();
            $data = array('must_check' => 'checked', 'text' => $inputChar);
            $query = array('data' => $data, 'actionName' => 'TestExampleAction',
                           'form' => array('className' => 'CM_Form_MockForm', 'params' => array(), 'id' => 'mockFormId'));
            $request->expects($this->any())->method('getQuery')->will($this->returnValue($query));
            /** @var CM_Request_Post $request */

            $response = new CM_Response_View_Form($request);
            $response->process();

            try {
                $this->assertFormResponseError($response, null, 'text');
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                $this->assertNotSame($inputChar, self::$formActionData->getString('text'));
            }
        }
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

    public function setup() {
        $this->registerField('must_check', new CM_FormField_Boolean());
        $this->registerField('color', new CM_FormField_Color());
        $this->registerField('text', new CM_FormField_Text());
        $this->registerField('array', new CM_FormField_Text());
        $this->registerAction(new CM_FormAction_MockForm_TestExampleAction($this));
    }
}

class CM_FormAction_MockForm_TestExampleAction extends CM_FormAction_Abstract {

    protected function _getRequiredFields() {
        return array('must_check', 'text');
    }

    protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
        CM_Form_AbstractTest::$formActionProcessCount++;
        CM_Form_AbstractTest::$formActionData = $params;
    }
}
