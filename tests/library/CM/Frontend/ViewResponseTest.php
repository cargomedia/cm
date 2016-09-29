<?php

class CM_Frontend_ViewResponseTest extends CMTest_TestCase {

    public function testSetGetTemplate() {
        /** @var CM_View_Abstract $viewMock */
        $viewMock = $this->getMockBuilder('CM_View_Abstract')->getMock();
        $viewResponse = new CM_Frontend_ViewResponse($viewMock);

        $this->assertSame('default', $viewResponse->getTemplateName());
        $viewResponse->setTemplateName('foo');
        $this->assertSame('foo', $viewResponse->getTemplateName());
    }

    public function testGetCssClasses() {
        $mockBuilder = $this->getMockBuilder('CM_View_Abstract');
        $mockBuilder->setMethods(['getClassHierarchy']);
        $viewMock = $mockBuilder->getMock();
        $classNames = [
            'foo',
            'bar',
        ];
        $viewMock->expects($this->any())->method('getClassHierarchy')->will($this->returnValue($classNames));
        /** @var CM_View_Abstract $viewMock */
        $viewResponse = new CM_Frontend_ViewResponse($viewMock);
        $this->assertSame($classNames, $viewResponse->getCssClasses());

        $viewResponse->addCssClass('jar');
        $classNames[] = 'jar';
        $this->assertSame($classNames, $viewResponse->getCssClasses());

        $viewResponse->setTemplateName('zoo');
        $classNames[] = 'zoo';
        $this->assertSame($classNames, $viewResponse->getCssClasses());

        $viewResponse->addCssClass('zoo');
        $this->assertSame($classNames, $viewResponse->getCssClasses());
    }

    public function testAddGetSetDataHtml() {
        /** @var CM_View_Abstract $viewMock */
        $viewMock = $this->getMockBuilder('CM_View_Abstract')->getMock();
        $viewResponse = new CM_Frontend_ViewResponse($viewMock);

        $this->assertSame([], $viewResponse->getDataHtml());
        $this->assertSame('', $viewResponse->getDataHtmlFormatted());
        $viewResponse->setDataHtml(['foo' => 'bar', 'baz' => 'quux']);
        $this->assertSame(
            [
                'foo' => 'bar',
                'baz' => 'quux'
            ], $viewResponse->getDataHtml());

        $viewResponse->addDataHtml('fooBar', 'barFoo');
        $this->assertSame(
            [
                'foo'    => 'bar',
                'baz'    => 'quux',
                'fooBar' => 'barFoo'
            ], $viewResponse->getDataHtml());
        $this->assertSame('data-foo="bar" data-baz="quux" data-fooBar="barFoo" ', $viewResponse->getDataHtmlFormatted());
    }

}
