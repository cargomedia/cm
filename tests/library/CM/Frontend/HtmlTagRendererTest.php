<?php

class CM_Frontend_HtmlTagRendererTest extends CMTest_TestCase {

    public function testRenderTag() {
        $tagRenderer = new CM_Frontend_HtmlTagRenderer();
        $tag = $tagRenderer->renderTag('div', 'foo bar',
            [
                'id'    => '123321',
                'class' => 'baz quux',
            ],
            [
                'foo' => 'bar',
                'baz' => 'quux',
            ]);
        $this->assertSame('<div id="123321" class="baz quux" data-foo="bar" data-baz="quux">foo bar</div>', $tag);

        $tag = $tagRenderer->renderTag('span');
        $this->assertSame('<span></span>', $tag);

        $exception = $this->catchException(function () use ($tagRenderer) {
            $tagRenderer->renderTag('');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Empty element name', $exception->getMessage());
    }
}
