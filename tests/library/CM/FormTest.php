<?php

class CM_FormTest extends CMTest_TestCase {

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
			"action"    => "form_test_example_action",
			"classname" => "CM_Form_FormTestExampleForm",
			"data"      => array("color" => "#123123", "must_check" => "checked"));
	}
}

class CM_Form_FormTestExampleForm extends CM_Form_Abstract {

	public function __construct() {
		parent::__construct('form_FormTestExampleForm');
	}

	public function setup() {
		$this->registerField(new CM_FormField_Boolean('must_check'));
		$this->registerField(new CM_FormField_Color('color'));
		$this->registerAction(new CM_formAction_FormTestExampleAction());
	}
}

class CM_FormAction_FormTestExampleAction extends CM_FormAction_Abstract {

	public function setup(CM_Form_Abstract $form) {
		$this->_fieldListRequired = array('must_check');
		parent::setup($form);
	}

	protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		CM_FormTest::$formActionProcessCount++;
	}
}
