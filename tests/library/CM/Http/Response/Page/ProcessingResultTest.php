<?php

class CM_Http_Response_Page_ProcessingResultTest extends CMTest_TestCase {

    public function testHtml() {
        $processor = new CM_Http_Response_Page_ProcessingResult();

        $this->assertSame(false, $processor->hasHtml());
        $this->assertInstanceOf('CM_Exception', $this->catchException(function () use ($processor) {
            $processor->getHtml();
        }));

        $processor->setHtml('foo');
        $this->assertSame(true, $processor->hasHtml());
        $this->assertSame('foo', $processor->getHtml());
    }

    public function testPage() {
        $processor = new CM_Http_Response_Page_ProcessingResult();
        $this->assertSame(false, $processor->hasPage());
        $this->assertInstanceOf('CM_Exception', $this->catchException(function () use ($processor) {
            $processor->getPage();
        }));
        $this->assertInstanceOf('CM_Exception', $this->catchException(function () use ($processor) {
            $processor->getPageInitial();
        }));

        /** @var CM_Page_Abstract $page1 */
        $page1 = $this->mockObject('CM_Page_Abstract');
        $processor->addPage($page1);

        /** @var CM_Page_Abstract $page2 */
        $page2 = $this->mockObject('CM_Page_Abstract');
        $processor->addPage($page2);

        $this->assertSame(true, $processor->hasPage());
        $this->assertSame($page2, $processor->getPage());
        $this->assertSame($page1, $processor->getPageInitial());
    }

    public function testPath() {
        $processor = new CM_Http_Response_Page_ProcessingResult();
        $this->assertSame([], $processor->getPathList());
        $this->assertSame(false, $processor->hasPath());
        $this->assertInstanceOf('CM_Exception', $this->catchException(function () use ($processor) {
            $processor->getPathInitial();
        }));

        $processor->addPath('/foo1');
        $processor->addPath('/foo2');

        $this->assertSame(true, $processor->hasPath());
        $this->assertSame('/foo1', $processor->getPathInitial());
        $this->assertSame(['/foo1', '/foo2'], $processor->getPathList());
    }

    public function testGetPathTracking() {
        $processor = new CM_Http_Response_Page_ProcessingResult();
        $this->assertInstanceOf('CM_Exception', $this->catchException(function () use ($processor) {
            $processor->getPathTracking();
        }));

        $processor->addPath('/foo1');
        $this->assertSame('/foo1', $processor->getPathTracking());

        /** @var CM_Page_Abstract|\Mocka\AbstractClassTrait $page */
        $page = $this->mockObject('CM_Page_Abstract');
        $page->mockMethod('getPathVirtualPageView')->set('/foo2');
        $processor->addPage($page);

        $this->assertSame('/foo2', $processor->getPathTracking());
    }

}
