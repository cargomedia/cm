<?php

class CM_Page_Error_AuthRequiredTest extends CMTest_TestCase {

    public function testGuest() {
        $page = $this->_createPage('CM_Page_Error_AuthRequired');
        $html = $this->_renderPage($page);

        $this->assertContains('Authentication required', $html->getText());
    }
}
