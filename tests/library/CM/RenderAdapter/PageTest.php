<?php

class CM_RenderAdapter_PageTest extends CMTest_TestCase {

    public function testFetchDescriptionKeywordsTitle() {
        $render = new CM_Render();

        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */

        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Page')
            ->setMethods(array('_fetchMetaTemplate'))
            ->setConstructorArgs(array($render, $page))
            ->getMock();
        $renderAdapter->expects($this->any())->method('_fetchMetaTemplate')->will($this->returnCallback(function ($tplName) {
            return 'test-' . $tplName . '.tpl';
        }));
        /** @var CM_RenderAdapter_Page $renderAdapter */

        $this->assertSame('test-meta-description.tpl', $renderAdapter->fetchDescription());
        $this->assertSame('test-meta-keywords.tpl', $renderAdapter->fetchKeywords());
        $this->assertSame('test-title.tpl', $renderAdapter->fetchTitle());
    }
}
