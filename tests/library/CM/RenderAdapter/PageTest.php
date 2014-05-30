<?php

class CM_RenderAdapter_PageTest extends CMTest_TestCase {

    public function testFetchDescriptionKeywordsTitleTrimming() {
        $render = new CM_Frontend_Render();

        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */

        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Page')
            ->setMethods(array('_fetchTemplate'))
            ->setConstructorArgs(array($render, $page))
            ->getMock();
        $renderAdapter->expects($this->any())->method('_fetchTemplate')->will($this->returnCallback(function ($templateName) {
            return "\n \t test-" . $templateName . "\n";
        }));
        /** @var CM_RenderAdapter_Page $renderAdapter */

        $this->assertSame('test-meta-description', $renderAdapter->fetchDescription());
        $this->assertSame('test-meta-keywords', $renderAdapter->fetchKeywords());
        $this->assertSame('test-title', $renderAdapter->fetchTitle());
    }
}
