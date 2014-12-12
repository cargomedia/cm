<?php

class CM_Page_ExampleTest extends CMTest_TestCase {

    /** @var bool */
    private $_debugBackup;

    protected function setUp() {
        $this->_debugBackup = CM_Bootloader::getInstance()->isDebug();
    }

    protected function tearDown() {
        CM_Bootloader::getInstance()->setDebug($this->_debugBackup);
    }

    public function testAccessible() {
        $page = new CM_Page_Example();

        CM_Bootloader::getInstance()->setDebug(true);
        $this->_renderPage($page);

        CM_Bootloader::getInstance()->setDebug(false);
        $this->assertPageNotRenderable($page);
    }

    public function testTidy() {
        CM_Bootloader::getInstance()->setDebug(true);
        $page = $this->_createPage('CM_Page_Example');
        $html = $this->_renderPage($page);
        $this->assertTidy($html, false);
    }
}
