<?php

class CMService_GoogleTagManager_ClientTest extends CMTest_TestCase {

    public function testCreate() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $environment = new CM_Frontend_Environment();

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertNotContains('dataLayer.push(', $html);
        $this->assertSame('', $js);
    }

    public function testCreateWithUser() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $viewer = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $viewer);

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertNotContains('dataLayer.push(', $html);
        $this->assertSame('', $js);
    }

    public function testTrackPageView() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $environment = new CM_Frontend_Environment();
        $gtm->trackPageView($environment, '/');

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertContains('dataLayer.push({"event":"Page View"});', $html);
        $this->assertSame('dataLayer.push({"event":"Page View"});', $js);
    }

    public function testTrackPageViewWithUser() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $viewer = CMTest_TH::createUser();
        $userId = $viewer->getId();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $gtm->trackPageView($environment, '/');

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertContains('dataLayer.push({"event":"Page View","username":"user' . $userId . '"});', $html);
        $this->assertSame('dataLayer.push({"event":"Page View","username":"user' . $userId . '"});', $js);
    }

    public function testTrackPageViewWithSplittest() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $request = CM_Http_Request_Abstract::factory('get', '/');
        $clientDevice = new CM_Http_ClientDevice($request);
        $environment = new CM_Frontend_Environment(null, null, null, null, null, null, null, $clientDevice);
        $splittest = CM_Model_Splittest_RequestClient::create('foo1', ['bar1']);
        $splittest->isVariationFixture($request, 'bar1');
        CM_Model_Splittest_RequestClient::create('foo2', ['bar2']);
        $gtm->trackPageView($environment, '/');

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertContains('dataLayer.push({"event":"Page View","Splittest foo1":"bar1"});', $html);
        $this->assertSame('dataLayer.push({"event":"Page View","Splittest foo1":"bar1"});', $js);
    }

    public function testTrackPageViewWithUserAndSplittest() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $viewer = CMTest_TH::createUser();
        $userId = $viewer->getId();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $splittest = CM_Model_Splittest_User::create('foo3', ['bar3']);
        $splittest->isVariationFixture($viewer, 'bar3');
        CM_Model_Splittest_User::create('foo4', ['bar4']);
        $gtm->trackPageView($environment, '/');

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertContains('dataLayer.push({"event":"Page View","Splittest foo3":"bar3","username":"user' . $userId . '"});', $html);
        $this->assertSame('dataLayer.push({"event":"Page View","Splittest foo3":"bar3","username":"user' . $userId . '"});', $js);
    }

    public function testTrackPageViewWithUserAndSeveralSplittests() {
        $gtm = new CMService_GoogleTagManager_Client('GTM-123456');
        $viewer = CMTest_TH::createUser();
        $userId = $viewer->getId();
        $environment = new CM_Frontend_Environment(null, $viewer);
        $splittest = CM_Model_Splittest_User::create('foo5', ['bar5']);
        $splittest->isVariationFixture($viewer, 'bar5');
        $splittest = CM_Model_Splittest_User::create('foo6', ['bar6']);
        $splittest->isVariationFixture($viewer, 'bar6');
        $gtm->trackPageView($environment, '/');

        $html = $gtm->getHtml($environment);
        $js = $gtm->getJs();
        $this->assertContains('"//www.googletagmanager.com/ns.html?id=GTM-123456"', $html);
        $this->assertContains('(window,document,\'script\',\'dataLayer\',\'GTM-123456\')', $html);
        $this->assertContains('dataLayer.push({"event":"Page View","Splittest foo5":"bar5","Splittest foo6":"bar6","username":"user' . $userId .
            '"});', $html);
        $this->assertSame('dataLayer.push({"event":"Page View","Splittest foo5":"bar5","Splittest foo6":"bar6","username":"user' . $userId .
            '"});', $js);
    }

}
