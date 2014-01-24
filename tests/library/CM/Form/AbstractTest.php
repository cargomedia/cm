<?php

class CM_Form_AbstractTest extends CMTest_TestCase {

	public static $formActionProcessCount = 0;

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
	}

	/**
	 * @return array
	 */
	private function _getData() {
		return array(
			"action"    => "TestExampleAction",
			"classname" => "CM_Form_MockForm",
			"data"      => array("color" => "#123123", "must_check" => "checked"));
	}
}

class CM_Form_MockForm extends CM_Form_Abstract {

	public function setup() {
		$this->registerField('must_check', new CM_FormField_Boolean());
		$this->registerField('color', new CM_FormField_Color());
		$this->registerAction(new CM_FormAction_MockForm_TestExampleAction($this));
	}
}

class CM_FormAction_MockForm_TestExampleAction extends CM_FormAction_Abstract {

	protected function _getRequiredFields() {
		return array('must_check');
	}

	protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		CM_Form_AbstractTest::$formActionProcessCount++;
	}
}
