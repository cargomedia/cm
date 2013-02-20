<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/modifier.usertext2.php';

class smarty_modifier_usertext2 extends CMTest_TestCase {
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

	public function testGetPlain() {
		$this->_assertSame('<span class="usertext2">foo</span>', array('text' => 'foo', 'mode'=>'plain'));
	}

	public function testGetMarkdown() {
		$this->_assertSame('<span class="usertext2"><p>foo</p></span>', array('text' => 'foo', 'mode'=>'markdown'));
	}

	private function _assertSame($expected, array $params) {
		$text = $params['text'] ? $params['text'] : null;
		$mode = $params['mode'] ? $params['mode'] : null;
		$lengthMax = $params['lengthMax'] ? $params['lengthMax'] : null;
		$stripEmoji = $params['stripEmoji'] ? $params['stripEmoji'] : null;
		$preserveParagraph = $params['preserveParagraph'] ? $params['preserveParagraph'] : null;
		$this->assertSame($expected, smarty_modifier_usertext2($text, $mode, $lengthMax, $stripEmoji, $preserveParagraph));
	}
}
