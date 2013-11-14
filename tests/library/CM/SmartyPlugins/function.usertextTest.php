<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.usertext.php';

class smarty_function_usertextTest extends CMTest_TestCase {

	/** @var Smarty_Internal_Template */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$render = new CM_Render();
		$this->_template = $smarty->createTemplate('string:');
		$this->_template->assignGlobal('render', $render);
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testModeEscape() {
		$this->_assertSame('foo&lt;', array('text' => 'foo<', 'mode' => 'escape'));
	}

	public function testModeOneline() {
		$this->_assertSame('<div class="usertext oneline">foo</div>', array('text' => 'foo', 'mode' => 'oneline'));
	}

	public function testModeSimple() {
		$this->_assertSame("<div class=\"usertext simple\">foo<br />\nbar</div>", array('text' => "foo  \nbar   \n", 'mode' => 'simple'));
	}

	public function testModeMarkdown() {
		$this->_assertSame("<div class=\"usertext markdown\"><h1>Headline</h1>\n<p>foo</p>\n<p><a href=\"http://www.google.com\">google.com</a></p></div>",
			array('text' => "#Headline#\nfoo\n[google.com](http://www.google.com)\n\n", 'mode' => 'markdown'));
	}

	public function testModeMarkdownSkipAnchors() {
		$this->_assertSame("<div class=\"usertext markdown\"><h1>Headline</h1>\n<p>foo</p>\n<p>google.com</p></div>",
			array('text' => "#Headline#\nfoo\n[google.com](http://www.google.com)\n\n", 'mode' => 'markdown', 'skipAnchors' => true));
	}

	public function testModeMarkdownPlain() {
		$this->_assertSame("<div class=\"usertext markdownPlain\">Headline\nfoo\n</div>", array('text' => "#Headline#\nfoo\n",
																								  'mode' => 'markdownPlain'));
	}

	public function testMaxLength() {
		$this->_assertSame("<div class=\"usertext oneline\">Hello…</div>", array('text' => "Hello World", 'mode' => 'oneline', 'maxLength' => 10));
		$this->_assertSame("<div class=\"usertext simple\">Hello…</div>", array('text' => "Hello World", 'mode' => 'simple', 'maxLength' => 10));
		$this->_assertSame("<div class=\"usertext markdownPlain\">Hello…</div>", array('text' => "Hello \n\n* World",
																						 'mode' => 'markdownPlain', 'maxLength' => 10));
		try {
			smarty_function_usertext(array('text' => 'foo', 'mode' => 'markdown', 'maxLength' => 1), $this->_template);
		} catch (CM_Exception_Invalid $ex) {
			$this->assertSame('MaxLength is not allowed in mode markdown.', $ex->getMessage());
		}
	}

	public function testModeNo() {
		try {
			smarty_function_usertext(array('text' => 'foo'), $this->_template);
		} catch (ErrorException $ex) {
			$this->assertSame('E_NOTICE: Undefined index: mode', $ex->getMessage());
		}
	}

	public function testModeWrong() {
		try {
			smarty_function_usertext(array('text' => 'foo', 'mode' => 'foo'), $this->_template);
		} catch (CM_Exception_Invalid $ex) {
			$this->assertSame('Invalid mode `foo`', $ex->getMessage());
		}
	}

	public function testIsMail() {
		$emoticonId = CM_Db_Db::insert('cm_emoticon', array('code' => ':smiley:', 'codeAdditional' => ':-)', 'file' => '1.png'));

		$this->_assertSame(
			'<div class="usertext oneline">foo <img src="http://www.default.dev/layout//' . CM_App::getInstance()->getDeployVersion() . '/img/emoticon/1.png" class="emoticon emoticon-' .
					$emoticonId . '" title=":smiley:" height="16" /></div>',
			array('text' => 'foo :-)', 'mode' => 'oneline', 'isMail' => true));
	}

	private function _assertSame($expected, array $params) {
		$this->assertSame($expected, smarty_function_usertext($params, $this->_template));
	}
}
