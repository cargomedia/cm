<?php

class CM_Http_Response_Resource_Css_VendorTest extends CMTest_TestCase {

    public function testProcess() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('vendor-css', 'all.css'));
        $response = CM_Http_Response_Resource_Css_Vendor::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('body{', $response->getContent());
        $this->assertNotContains('#mocha', $response->getContent());
    }
}
