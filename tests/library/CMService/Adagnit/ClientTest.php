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
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->addEvent('signup', ['location' => 'USA']);
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup, {"location":"USA"});', $js);

        $adagnit->addEvent('purchaseSuccess', ['value' => 123]);
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup, {"location":"USA"});', $js);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"value":123});', $js);
    }

    public function testAddPageView() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.view(', $js);

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs($environment);
        $this->assertSame(2, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(2, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->addPageView('/bar');
        $js = $adagnit->getJs($environment);
        $this->assertSame(3, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(2, substr_count($js, 'ADGN.track.view("\/foo");'));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/bar");'));
    }

    public function testSetPageView() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.view(', $js);

        $adagnit->addPageView('/foo');
        $js = $adagnit->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/foo");'));

        $adagnit->setPageView('/bar');
        $js = $adagnit->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ADGN.track.view('));
        $this->assertSame(1, substr_count($js, 'ADGN.track.view("\/bar");'));
    }

    public function testTrackLogin() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->trackLogin();
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.login);', $js);
    }

    public function testTrackSignUp() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->trackSignUp();
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.signup);', $js);
    }

    public function testAddSale() {
        $adagnit = new CMService_Adagnit_Client();
        $environment = new CM_Frontend_Environment();
        $js = $adagnit->getJs($environment);
        $this->assertNotContains('ADGN.track.event(', $js);

        $adagnit->addSale('t123', 'p123', 12.34);
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"transactionId":"t123","productId":"p123","amount":12.34});', $js);

        $adagnit->addSale('t456', 'p456', 56.78);
        $js = $adagnit->getJs($environment);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"transactionId":"t123","productId":"p123","amount":12.34});', $js);
        $this->assertContains('ADGN.track.event(ADGN.eventTypes.purchaseSuccess, {"transactionId":"t456","productId":"p456","amount":56.78});', $js);
    }
}
