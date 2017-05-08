<?php

class CM_Http_Response_Resource_Javascript_VendorTest extends CMTest_TestCase {

    public function testProcessBeforeBody() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('vendor-js', 'before-body.js'));
        $response = CM_Http_Response_Resource_Javascript_Vendor::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('function()', $response->getContent());
        $this->assertContains('Modernizr', $response->getContent());
    }

    public function testProcessAfterBody() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('vendor-js', 'after-body.js'));
        $response = CM_Http_Response_Resource_Javascript_Vendor::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('function()', $response->getContent());
        $this->assertContains('jQuery', $response->getContent());
    }
}
