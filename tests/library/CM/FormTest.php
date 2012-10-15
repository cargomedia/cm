<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_FormTest extends TestCase {

	public static $formActionProcessCount = 0;

	function testForm() {
		$data = $this->_getData();
		self::$formActionProcessCount = 0;
		$response = $this->getMockFormResponse($data['classname'], $data['action'], $data['data']);
		$this->assertSame(1, self::$formActionProcessCount);
		$this->assertNotContains('errors', array_keys($response));
	}

	function testMissingField() {
		$data = $this->_getData();
		unset($data['data']['must_check']);
		$response = $this->getMockFormResponse($data['classname'], $data['action'], $data['data']);
		$this->assertContains('errors', array_keys($response));
	}

	function testAllowedMissingField() {
		$data = $this->_getData();
		unset($data['data']['color']);
		$response = $this->getMockFormResponse($data['classname'], $data['action'], $data['data']);
		$this->assertNotContains('errors', array_keys($response));
	}

	/**
	 * @return array
	 */
	private function _getData() {
		return array("action" => "return_input_parameters", "classname" => "CM_Form_FormTestExampleForm",
				"data" => array("color" => "#123123",
						"must_check" => "checked"));
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

class CM_formAction_FormTestExampleAction extends CM_FormAction_Abstract {
	public function __construct() {
		parent::__construct('return_input_parameters');
	}
	public function setup(CM_Form_Abstract $form) {
		$this->required_fields = array('must_check');
		parent::setup($form);
	}
	public function process(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		CM_FormTest::$formActionProcessCount++;
	}
}
