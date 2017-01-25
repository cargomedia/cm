<?php

class CM_RenderAdapter_PageTest extends CMTest_TestCase {

    public function testFetchDescriptionKeywordsTitleTrimming() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        $render = new CM_Frontend_Render();

        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */

        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Page')
            ->setMethods(array('_getMetaTemplatePath'))
            ->setConstructorArgs(array($render, $page))
            ->getMock();
        $renderAdapter->expects($this->any())->method('_getMetaTemplatePath')->will($this->returnCallback(function ($templateName) use ($dirTmp) {
            $templateFile = new CM_File($dirTmp . $templateName);
            $templateFile->ensureParentDirectory();
            $templateFile->write("\n \t test-" . $templateName . "\n");
            return $templateFile->getPath();
        }));
        /** @var CM_RenderAdapter_Page $renderAdapter */

        $this->assertSame('test-meta-description', $renderAdapter->fetchDescription());
        $this->assertSame('test-meta-keywords', $renderAdapter->fetchKeywords());
        $this->assertSame('test-title', $renderAdapter->fetchTitle());
    }

    public function testFetchDescriptionKeywordsConsiderNamespaceWideLocation() {
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();

        $render = $this->getMockBuilder('CM_Frontend_Render')->setMethods(['getTemplatePath', 'getLayoutPath'])->getMock();
        $render->expects($this->any())->method('getTemplatePath')->will($this->returnValue(null));
        $render->expects($this->exactly(2))->method('getLayoutPath')->will($this->returnCallback(function ($templateName) use ($dirTmp) {
            $templateFile = new CM_File($dirTmp . $templateName);
            $templateFile->ensureParentDirectory();
            $templateFile->write('test-' . $templateName);
            return $templateFile->getPath();
        }));
        /** @var CM_Frontend_Render $render */

        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */

        $renderAdapter = new CM_RenderAdapter_Page($render, $page);
        $this->assertSame('test-Page/Abstract/meta-description.tpl', $renderAdapter->fetchDescription());
        $this->assertSame('test-Page/Abstract/meta-keywords.tpl', $renderAdapter->fetchKeywords());
    }
}
