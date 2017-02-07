<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.tag.php';

class smarty_function_TagTest extends CMTest_TestCase {

    public function testRender() {
        $smarty = new Smarty();
        $template = $smarty->createTemplate('string:');

        $this->assertContainsAll(
            ['div', 'data-foo="3"', 'data-bar="baz"', 'foo bar'],
            smarty_function_tag(['el' => 'div', 'content' => 'foo bar', 'data' => ['foo' => 3, 'bar' => 'baz']], $template)
        );

        $exception = $this->catchException(function () use ($template) {
            smarty_function_tag(['content' => 'foo bar'], $template);
        });
        $this->assertInstanceOf('ErrorException', $exception);
        $this->assertContains('Param `el` missing.', $exception->getMessage());

        $exception = $this->catchException(function () use ($template) {
            smarty_function_tag(['el' => 'span', 'data' => 'foo bar'], $template);
        });
        $this->assertInstanceOf('ErrorException', $exception);
        $this->assertContains('Param `data` should be an array.', $exception->getMessage());
    }
}
