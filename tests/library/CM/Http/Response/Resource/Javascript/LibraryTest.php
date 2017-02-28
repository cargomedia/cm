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
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', 'library.js'));
        $response = CM_Http_Response_Resource_Javascript_Library::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('function()', $response->getContent());
    }

    public function testProcessTranslations() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', 'translations/123.js'));
        $response = CM_Http_Response_Resource_Javascript_Library::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();

        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('cm.language.setAll', $response->getContent());
    }

    public function testProcessTranslationsEnableKey() {
        $language = CMTest_TH::createLanguage('test');
        $languageKey = CM_Model_LanguageKey::create('Hello World');
        $this->assertSame(false, $languageKey->getJavascript());

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, null, $language));

        // Check that key is *not* included in the JS translations list
        $versionJavascript = CM_Model_Language::getVersionJavascript();
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', "translations/{$versionJavascript}.js"));
        $response = CM_Http_Response_Resource_Javascript_Library::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertNotContains('Hello World', $response->getContent());

        // Request JS-enabling of language key via RPC call
        $body = CM_Params::jsonEncode([
            'method' => 'CM_Model_Language.requestTranslationJs',
            'params' => ['Hello World'],
        ]);
        $request = new CM_Http_Request_Post('/rpc', null, null, $body);
        $response = CM_Http_Response_RPC::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        CMTest_TH::reinstantiateModel($languageKey);
        $this->assertSame(true, $languageKey->getJavascript());

        // Check that key *is* included in the JS translations list
        $versionJavascript = CM_Model_Language::getVersionJavascript();
        $request = new CM_Http_Request_Get($render->getUrlResource('library-js', "translations/{$versionJavascript}.js"));
        $response = CM_Http_Response_Resource_Javascript_Library::createFromRequest($request, $site, $this->getServiceManager());
        $response->process();
        $this->assertContains('Hello World', $response->getContent());
    }
}
