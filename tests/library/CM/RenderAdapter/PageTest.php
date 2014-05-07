<?php

class CM_RenderAdapter_PageTest extends CMTest_TestCase {

    public function testFetchDescriptionKeywordsTitle() {
        $render = new CM_Render();
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();

        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */

        $renderAdapter = $this->getMockBuilder('CM_RenderAdapter_Page')
            ->setMethods(array('_getTplPath'))
            ->setConstructorArgs(array($render, $page))
            ->getMock();
        $renderAdapter->expects($this->any())->method('_getTplPath')->will($this->returnCallback(function ($tplName) use($dirTmp) {
            $template = "\n \t" . 'test-' . $tplName . "\n";
            return CM_File::create($dirTmp . $tplName, $template)->getPath();
        }));
        /** @var CM_RenderAdapter_Page $renderAdapter */

        $this->assertSame('test-meta-description.tpl', $renderAdapter->fetchDescription());
        $this->assertSame('test-meta-keywords.tpl', $renderAdapter->fetchKeywords());
        $this->assertSame('test-title.tpl', $renderAdapter->fetchTitle());
    }
}
