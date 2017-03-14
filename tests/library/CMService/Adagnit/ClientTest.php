<?php

class CMService_Adagnit_ClientTest extends CMTest_TestCase {

    public function testCreate() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $html = $adagnit->getHtml($environment);
        $this->assertContains('<script type="text/javascript" src="https://via.adagnit.io/static/view/js/ada.js"></script>', $html);
    }

    public function testAddEvent() {
        $adagnit = new CMService_Adagnit_Client();
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->addEvent('signup', ['location' => 'USA']);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup, {"location":"USA"});', $js);

        $adagnit->addEvent('purchaseSuccess', ['value' => 123]);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup, {"location":"USA"});', $js);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"value":123});', $js);
    }

    public function testAddEventInvalid() {
        $adagnit = new CMService_Adagnit_Client();
        $exception = $this->catchException(function () use ($adagnit) {
            $adagnit->addEvent('invalid');
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Unknown event type', $exception->getMessage());
        $this->assertSame(['eventType' => 'invalid'], $exception->getMetaInfo());
    }

    public function testAddPageView() {
        $adagnit = new CMService_Adagnit_Client();
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.view(', $js);

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs();
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs();
        $this->assertSame(2, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(2, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->addPageView('/bar');
        $js = $adagnit->getJs();
        $this->assertSame(3, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(2, substr_count($js, 'ADGN.track.view("\/foo");'));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/bar");'));
    }

    public function testSetPageView() {
        $adagnit = new CMService_Adagnit_Client();
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.view(', $js);

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs();
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->setPageView('/bar');
        $js = $adagnit->getJs();
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/bar");'));
    }

    public function testTrackSignIn() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($site, $user);
        $adagnit = new CMService_Adagnit_Client();

        $adagnit->trackSignIn($environment);
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->trackPageView($environment);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.login, {"site":"www.my-website.net"});', $js);
    }

    public function testTrackSignUp() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($site, $user);
        $adagnit = new CMService_Adagnit_Client();

        $adagnit->trackSignUp($environment);
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->trackPageView($environment);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup, {"site":"www.my-website.net"});', $js);
    }

    public function testTrackSale() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($site, $user);
        $adagnit = new CMService_Adagnit_Client();

        $adagnit->trackSale($environment);
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->trackPageView($environment);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"site":"www.my-website.net"});', $js);
    }

    public function testTtl() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($site, $user);
        $adagnit = new CMService_Adagnit_Client(1);
        $adagnit->trackSale($environment);

        $adagnit->trackPageView($environment);
        $js = $adagnit->getJs();
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"site":"www.my-website.net"});', $js);
    }

    public function testTtlExpired() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment($site, $user);
        $adagnit = new CMService_Adagnit_Client(0);
        $adagnit->trackSale($environment);

        $adagnit->trackPageView($environment);
        $js = $adagnit->getJs();
        $this->assertNotContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"site":"www.my-website.net"});', $js);
    }
}
