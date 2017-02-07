<?php

class CM_View_DocumentTest extends CMTest_TestCase {

    /** @var CM_Model_Language */
    private $_languageDefault;

    protected function setUp() {
        $this->_languageDefault = CMTest_TH::createLanguage('en');
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testRender() {
        $site = $this->getMockSite('CM_Site_Abstract', null, [
            'url'  => 'http://www.my-website.net',
            'name' => 'My website',
        ]);
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Document($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertContains('<html', $html);
        $this->assertContains('<title>My website</title>', $html);
        $this->assertContains('<body', $html);
        $this->assertContains('class="CM_Layout_Default', $html);
    }

    public function testTrackingDisabled() {
        $site = $this->getMockSite('CM_Site_Abstract', null, ['url' => 'http://www.my-website.net']);
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Document($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertNotContains('ga("create", "key"', $html);
        $this->assertNotContains('var _kmq = _kmq || [];', $html);
    }

    public function testTrackingGuest() {
        $siteMock = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $environment = new CM_Frontend_Environment($siteMock);
        $render = new CM_Frontend_Render($environment, $this->_getServiceManager('ga123', 'km123'));
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Document($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertNotContains('ga("send", "pageview"', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testTrackingViewer() {
        $site = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $mockBuilder = $this->getMockBuilder('CM_Model_User');
        $mockBuilder->setMethods(['getIdRaw', 'getVisible', 'getLanguage']);
        $viewerMock = $mockBuilder->getMock();
        $viewerMock->expects($this->any())->method('getIdRaw')->will($this->returnValue(array('id' => '1')));
        $viewerMock->expects($this->any())->method('getVisible')->will($this->returnValue(false));
        $viewerMock->expects($this->any())->method('getLanguage')->will($this->returnValue(null));
        /** @var CM_Model_User $viewerMock */
        $environment = new CM_Frontend_Environment($site, $viewerMock);
        $render = new CM_Frontend_Render($environment, $this->_getServiceManager('ga123', 'km123'));
        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $pageMock = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Mock' . uniqid());
        /** @var CM_Page_Abstract $pageMock */
        $renderAdapter = new CM_RenderAdapter_Document($render, $pageMock);
        $html = $renderAdapter->fetch();

        $this->assertContains('ga("create", "ga123"', $html);
        $this->assertNotContains('ga("send", "pageview"', $html);
        $this->assertContains('var _kmq = _kmq || [];', $html);
        $this->assertContains("var _kmk = _kmk || 'km123';", $html);
        $this->assertNotContains("_kmq.push(['identify'", $html);
        $this->assertNotContains("_kmq.push(['alias'", $html);
    }

    public function testLanguageAlternatives() {
        $site = $this->getMockSite('CM_Site_Abstract', null, array('url' => 'http://www.example.com'));
        $language1 = CMTest_TH::createLanguage('fr');
        $language2 = CMTest_TH::createLanguage('de');

        $environment = new CM_Frontend_Environment($site, null, $language2, null, true);
        $render = new CM_Frontend_Render($environment);

        $this->getMockForAbstractClass('CM_Layout_Abstract', array(), 'CM_Layout_Default');
        $page = new CM_Page_Example();
        $renderAdapter = new CM_RenderAdapter_Document($render, $page);
        $html = $renderAdapter->fetch();

        $this->assertContains('<link rel="alternate" href="http://www.example.com/example" hreflang="x-default">', $html);
        $this->assertContains('<link rel="alternate" href="http://www.example.com/fr/example" hreflang="fr">', $html);
        $this->assertContains('<link rel="alternate" href="http://www.example.com/de/example" hreflang="de">', $html);
    }

    /**
     * @param string $codeGoogleAnalytics
     * @param string $codeKissMetrics
     * @return CM_Service_Manager
     */
    protected function _getServiceManager($codeGoogleAnalytics, $codeKissMetrics) {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('googleanalytics', new CMService_GoogleAnalytics_Client($codeGoogleAnalytics));
        $serviceManager->registerInstance('kissmetrics', new CMService_KissMetrics_Client($codeKissMetrics));
        $serviceManager->registerInstance('trackings', new CM_Service_Trackings(['googleanalytics', 'kissmetrics']));
        return $serviceManager;
    }

}
