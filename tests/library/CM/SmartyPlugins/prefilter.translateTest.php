<?php
require_once __DIR__ . '/../../../TestCase.php';

require_once DIR_LIBRARY . 'CM/SmartyPlugins/prefilter.translate.php';

class smarty_prefilter_translateTest extends TestCase {

	/**
	 * @var Smarty_Internal_Template
	 */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$this->_template = $smarty->createTemplate('string:');
	}

	private function _assertSame($expected, $source) {
		$this->assertSame($expected, smarty_prefilter_translate($source, $this->_template));
	}

	public function testStatic() {
		$this->_assertSame("{translateStatic key='foo'}", "{translate 'foo'}");
		$this->_assertSame('{translateStatic key="foo bar"}', '{translate "foo bar"}');
		$this->_assertSame("{translateStatic key=foo}", "{translate foo}");
	}

	public function testVariable() {
		$this->_assertSame("{translateVariable key='foo' a=\$a}", "{translate 'foo' a=\$a}");
		$this->_assertSame("{translateVariable key=foo a=\$a}", "{translate foo a=\$a}");

		$this->_assertSame('{translateVariable key=$foo}', '{translate $foo}');
		$this->_assertSame('{translateVariable key="$foo"}', '{translate "$foo"}');
		$this->_assertSame('{translateVariable key="hello $foo there"}', '{translate "hello $foo there"}');
		$this->_assertSame('{translateVariable key="hello {$foo} there"}', '{translate "hello {$foo} there"}');
		$this->_assertSame('{translateVariable key={anything_here}}', '{translate {anything_here}}');


		$this->_assertSame('{translateVariable key="hello {foo} there"}', '{translate "hello {foo} there"}');
		$this->_assertSame("{translateVariable key='hello {\$a} there' a=\$a}", "{translate 'hello {\$a} there' a=\$a}");
		$this->_assertSame("{translateVariable key='hello {\$a} there' a=\$a->foo()}", "{translate 'hello {\$a} there' a=\$a->foo()}");

		$this->_assertSame('{translateVariable key="bar\"$foo"}', '{translate "bar\"$foo"}');
		$this->_assertSame('{translateVariable key="bar\$foo"}', '{translate "bar\$foo"}');
	}

	public function testVariableWithCurlybracketInKey() {
		$this->markTestSkipped('Not implemented');
		$this->_assertSame('{translateVariable "foo}" a=$a}', '{translate "foo}" a=$a}');
	}
}
