<?php

class CM_Page_Error_NotAllowedTest extends CMTest_TestCase {

    public function testGuest() {
        $page = $this->_createPage('CM_Page_Error_NotAllowed');
        $html = $this->_renderPage($page);

        $this->assertTrue($html->has('.CM_Component_NotAllowed'));
    }
}
