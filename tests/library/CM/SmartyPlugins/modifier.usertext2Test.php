<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/modifier.usertext2.php';

class smarty_modifier_usertext2Test extends CMTest_TestCase {
	/**
	 * @var Smarty_Internal_Template
	 */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$render = new CM_Render($this->_getSite());
		$this->_template = $smarty->createTemplate('string:');
		$this->_template->assignGlobal('render', $render);
	}

	public function testModeOneline() {
		$this->_assertSame('foo', array('text' => 'foo', 'mode' => 'oneline'));
	}

	public function testModeSimple() {
		$this->_assertSame("foo<br />\nbar", array('text' => "foo  \nbar   \n", 'mode' => 'simple'));
	}

	public function testModeFormat() {
		$this->_assertSame("<span class=\"usertext2\"><h1>Headline</h1>\n<p>foo</p></span>", array('text' => "#Headline#\nfoo\n\n",
			'mode' => 'format'));
	}

	public function testModePlain() {
		$this->_assertSame("Headline\nfoo\n", array('text' => "#Headline#\nfoo\n", 'mode' => 'plain'));
	}

	public function testModeNo() {
		try {
			$this->_assertSame('xxx', array('text' => 'foo'));
		} catch (CM_Exception_Invalid $ex) {
			$this->assertSame('Must define mode in Usertext.', $ex->getMessage());
		}
	}

	private function _assertSame($expected, array $params) {
		$text = $params['text'] ? $params['text'] : null;
		$mode = $params['mode'] ? $params['mode'] : null;
		$maxLength = $params['maxLength'] ? $params['maxLength'] : null;
		$this->assertSame($expected, smarty_modifier_usertext2($text, $mode, $maxLength));
	}
}
