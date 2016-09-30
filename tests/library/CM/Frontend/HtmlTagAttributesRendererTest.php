<?php

class CM_Frontend_HtmlTagAttributesRendererTest extends CMTest_TestCase {

    public function testRenderTagAttributes() {
        $viewResponseMock = $this->mockClass('CM_Frontend_ViewResponse')->newInstanceWithoutConstructor();
        $viewResponseMock->mockMethod('getAutoId')->set('foo');
        $viewResponseMock->mockMethod('getCssClasses')->set(['bar', 'baz']);
        $viewResponseMock->mockMethod('getDataHtml')->set(
            [
                'fooBar' => 'barBaz',
                'barFoo' => 'bazBar'
            ]
        );
        $tagAttributesRenderer = new CM_Frontend_HtmlTagAttributesRenderer($viewResponseMock);
        $this->assertSame('id="foo" class="bar baz" data-fooBar="barBaz" data-barFoo="bazBar"', $tagAttributesRenderer->renderTagAttributes());
    }
}
