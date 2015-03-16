<?php

class CM_Http_Response_Resource_Javascript_LibraryTest extends CMTest_TestCase {

    /** @var CM_File */
    private $_configInternalFile;

    protected function setUp() {
        CMTest_TH::createLanguage();

        $this->_configInternalFile = new CM_File(DIR_ROOT . 'resources/config/js/internal.js');
        $this->_configInternalFile->ensureParentDirectory();
        $this->_configInternalFile->write('console.log("hello world")');
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();

        $this->_configInternalFile->delete();
    }

    public function testProcessLibrary() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', 'library.js'));
        $response = new CM_Http_Response_Resource_Javascript_Library($request, $this->getServiceManager());
        $response->process();
        $this->assertContains('function()', $response->getContent());
    }

    public function testProcessTranslations() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', 'translations/123.js'));
        $response = new CM_Http_Response_Resource_Javascript_Library($request, $this->getServiceManager());
        $response->process();
        $this->assertContains('cm.language.setAll', $response->getContent());
    }
}
