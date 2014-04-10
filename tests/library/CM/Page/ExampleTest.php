<?php

class CM_Page_ExampleTest extends CMTest_TestCase {

    public function testAccessible() {
        $page = $this->_createPage('CM_Page_Example');

        $this->assertPageViewable($page);
    }

    public function testTidy() {
        $page = $this->_createPage('CM_Page_Example');
        $html = $this->_renderPage($page);

        $this->assertTidy($html, false);
    }
}
