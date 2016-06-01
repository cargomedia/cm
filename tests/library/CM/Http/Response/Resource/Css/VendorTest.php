<?php

class CM_Http_Response_Resource_Css_VendorTest extends CMTest_TestCase {

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('vendor-css', 'all.css'));
        $response = new CM_Http_Response_Resource_Css_Vendor($request, $this->getServiceManager());
        $response->process();
        $this->assertContains('body{', $response->getContent());
        $this->assertNotContains('#mocha', $response->getContent());
    }
}
