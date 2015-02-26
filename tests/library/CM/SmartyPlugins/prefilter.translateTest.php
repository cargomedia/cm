<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/prefilter.translate.php';

class smarty_prefilter_translateTest extends CMTest_TestCase {

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

    public function testPrefilter() {
        $this->_assertSame("{translate key='foo'}", "{translate 'foo'}");
        $this->_assertSame('{translate key="foo bar"}', '{translate "foo bar"}');
        $this->_assertSame("{translate key=foo}", "{translate foo}");

        $this->_assertSame("{translate key='foo' a=\$a}", "{translate 'foo' a=\$a}");
        $this->_assertSame("{translate key=foo a=\$a}", "{translate foo a=\$a}");

        $this->_assertSame('{translate key=$foo}', '{translate $foo}');
        $this->_assertSame('{translate key="$foo"}', '{translate "$foo"}');
        $this->_assertSame('{translate key="hello $foo there"}', '{translate "hello $foo there"}');
        $this->_assertSame('{translate key="hello {$foo} there"}', '{translate "hello {$foo} there"}');
        $this->_assertSame('{translate key={anything_here}}', '{translate {anything_here}}');

        $this->_assertSame('{translate key="hello {foo} there"}', '{translate "hello {foo} there"}');
        $this->_assertSame("{translate key='hello {\$a} there' a=\$a}", "{translate 'hello {\$a} there' a=\$a}");
        $this->_assertSame("{translate key='hello {\$a} there' a=\$a->foo()}", "{translate 'hello {\$a} there' a=\$a->foo()}");

        $this->_assertSame('{translate key="bar\"$foo"}', '{translate "bar\"$foo"}');
        $this->_assertSame('{translate key="bar\$foo"}', '{translate "bar\$foo"}');

        $this->_assertSame('{translate key="foo}" a=$a}', '{translate "foo}" a=$a}');
    }
}
