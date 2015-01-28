<?php

class CM_Http_Response_Page_EmbedTest extends CMTest_TestCase {

    public function testProcessHostRedirect() {
        $site = CM_Site_Abstract::factory();

        $response = CMTest_TH::createResponsePageEmbed('/mock7', array('host' => $site->getHost()));
        $response->process();
        $this->assertNull($response->getRedirectUrl());

        $response = CMTest_TH::createResponsePageEmbed('/mock7', array('host' => 'incorrect-host.org'));
        $response->process();
        $this->assertSame($site->getUrl() . '/mock7', $response->getRedirectUrl());
    }

    public function testProcessLanguageRedirect() {
        CMTest_TH::createLanguage('en');

        $response = CMTest_TH::createResponsePageEmbed('/en/mock7');
        $response->process();
        $this->assertNull($response->getRedirectUrl());

        $response = CMTest_TH::createResponsePageEmbed('/en/mock7', null, CMTest_TH::createUser());
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
