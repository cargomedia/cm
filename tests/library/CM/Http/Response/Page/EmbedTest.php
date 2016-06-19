<?php

class CM_Http_Response_Page_EmbedTest extends CMTest_TestCase {

    public function testProcessHostRedirect() {
        $site = CM_Site_Abstract::factory();
        $request = new CM_Http_Request_Get('/mock7', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page_Embed::createEmbedResponseFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertNull($response->getRedirectUrl());

        $request = new CM_Http_Request_Get('/mock7', ['host' => 'incorrect-host.org']);
        $response = CM_Http_Response_Page_Embed::createEmbedResponseFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertSame($site->getUrl() . '/mock7', $response->getRedirectUrl());
    }

    public function testProcessLanguageRedirect() {
        CMTest_TH::createLanguage('en');
        $site = CM_Site_Abstract::factory();
        $request = new CM_Http_Request_Get('/en/mock7', ['host' => $site->getHost()]);
        $response = CM_Http_Response_Page_Embed::createEmbedResponseFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertNull($response->getRedirectUrl());

        $site = CM_Site_Abstract::factory();
        $viewer = CMTest_TH::createUser();
        $request = new CM_Http_Request_Get('/en/mock7', ['host' => $site->getHost()], null, $viewer);
        $response = CM_Http_Response_Page_Embed::createEmbedResponseFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertSame($response->getSite()->getUrl() . '/mock7', $response->getRedirectUrl());
    }
}

class CM_Page_Mock7 extends CM_Page_Abstract {

    public function getLayout(CM_Frontend_Environment $environment, $layoutName = null) {

        return new CM_Layout_Mock2();
    }
}

class CM_Layout_Mock2 extends CM_Layout_Abstract {

}
