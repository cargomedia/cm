<?php

class CM_Page_ExampleTest extends CMTest_TestCase {

    public function testNotAccessible() {
        $debugBackup = CM_Bootloader::getInstance()->isDebug();
        CM_Bootloader::getInstance()->setDebug(false);

        $page = $this->_createPage('CM_Page_Example');
        $this->assertPageViewable($page);

        CM_Bootloader::getInstance()->setDebug($debugBackup);
    }

    public function testAccessible() {
        $debugBackup = CM_Bootloader::getInstance()->isDebug();

        CM_Bootloader::getInstance()->setDebug(true);
        $page = $this->_createPage('CM_Page_Example');
        $this->assertPageViewable($page);
        $this->_renderPage($page);

        CM_Bootloader::getInstance()->setDebug(false);
        $page = $this->_createPage('CM_Page_Example');
        $this->assertPageNotRenderable($page);

        CM_Bootloader::getInstance()->setDebug($debugBackup);
    }

    public function testTidy() {
        $debugBackup = CM_Bootloader::getInstance()->isDebug();

        CM_Bootloader::getInstance()->setDebug(true);
        $page = $this->_createPage('CM_Page_Example');
        $html = $this->_renderPage($page);
        $this->assertTidy($html, false);

        CM_Bootloader::getInstance()->setDebug($debugBackup);
    }
}
