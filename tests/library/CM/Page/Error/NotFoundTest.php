<?php

class CM_Page_Error_NotFoundTest extends CMTest_TestCase {

    public function testGuest() {
        $page = $this->_createPage('CM_Page_Error_NotFound');
        $html = $this->_renderPage($page);

        $this->assertTrue($html->exists('.CM_Component_Notfound'));
    }
}
