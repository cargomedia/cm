<?php

class CM_Frontend_ViewResponseTest extends CMTest_TestCase {

    public function testSetGetTemplate() {
        /** @var CM_View_Abstract $view */
        $view = $this->getMock('CM_View_Abstract');
        $viewResponse = new CM_Frontend_ViewResponse($view);

        $this->assertSame('default', $viewResponse->getTemplateName());
        $viewResponse->setTemplateName('foo');
        $this->assertSame('foo', $viewResponse->getTemplateName());
    }

    public function testGetCssClasses() {
        $view = $this->getMock('CM_View_Abstract', ['getClassHierarchy']);
        $classNames = [
            'foo',
            'bar',
        ];
        $view->expects($this->any())->method('getClassHierarchy')->will($this->returnValue($classNames));
        /** @var CM_View_Abstract $view */
        $viewResponse = new CM_Frontend_ViewResponse($view);
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
}
