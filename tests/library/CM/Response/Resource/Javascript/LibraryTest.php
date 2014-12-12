<?php

class CM_Response_Resource_Javascript_LibraryTest extends CMTest_TestCase {

    protected function setUp() {
        CMTest_TH::createLanguage();
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcessLibrary() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Request_Get($render->getUrlResource('library-js', 'library.js'));
        $response = new CM_Response_Resource_Javascript_Library($request);
        $response->process();
        $this->assertContains('function()', $response->getContent());
    }

    public function testProcessTranslations() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Request_Get($render->getUrlResource('library-js', 'translations/123.js'));
        $response = new CM_Response_Resource_Javascript_Library($request);
        $response->process();
        $this->assertContains('cm.language.setAll', $response->getContent());
    }
}
