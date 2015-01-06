<?php

class CM_Http_Response_Page_EmbedTest extends CMTest_TestCase {

    public function testProcessLanguageRedirect() {
        CMTest_TH::createLanguage('en');
        $user = CMTest_TH::createUser();
        $response = CMTest_TH::createResponsePageEmbed('/en/mock5', null, $user);
        $response->process();
        $this->assertSame($response->getSite()->getUrl() . '/mock5', $response->getRedirectUrl());
    }
}
