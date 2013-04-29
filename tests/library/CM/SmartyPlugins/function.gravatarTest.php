<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.gravatar.php';

class smarty_function_gravatarTest extends CMTest_TestCase {

	/**
	 * @var Smarty_Internal_Template
	 */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$render = new CM_Render();
		$this->_template = $smarty->createTemplate('string:');
		$this->_template->assignGlobal('render', $render);
	}

	public function testRender() {
		$this->_assertSame('<img src="https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0" />', array('email' => 'test@example.com'));
		$this->_assertSame('<img src="https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=140&amp;d=http%3A%2F%2Fexample.com%2Fdefault.jpg" />', array('email' => 'test@example.com',
			'size' => 140, 'default' => 'http://example.com/default.jpg'));
		$this->_assertSame('<img src="https://secure.gravatar.com/avatar/55502f40dc8b7c769880b10874abc9d0?s=140&amp;d=http%3A%2F%2Fexample.com%2Fdefault.jpg" class="TestClass" title="TestTitle" alt="TestTitle" width="20" height="20" />', array('email' => 'test@example.com',
			'size' => 140, 'default' => 'http://example.com/default.jpg', 'class' => 'TestClass', 'title' => 'TestTitle', 'width' => 20,
			'height' => 20));
	}

	/**
	 * @param string $expected
	 * @param array  $params
	 */
	private function _assertSame($expected, array $params) {
		$this->assertSame($expected, smarty_function_gravatar($params, $this->_template));
	}
}
