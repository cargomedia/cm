<?php

class CM_Response_Resource_Css_VendorTest extends CMTest_TestCase {

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Request_Get($render->getUrlResource('vendor-css', 'all.css'));
        $response = new CM_Response_Resource_Css_Vendor($request);
        $response->process();
        $this->assertContains('body{', $response->getContent());
    }
}
