<?php

class CMService_GoogleAnalytics_ClientTest extends CMTest_TestCase {

    public function testCreate() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $googleAnalytics = new CMService_GoogleAnalytics_Client('key123');
        $environment = new CM_Frontend_Environment($site);
        $request = new CM_Http_Request_Get('/pseudo-request');

        $html = $googleAnalytics->getHtml($environment);
        $this->assertContains('ga("create", "key123", {"cookieDomain":"www.my-website.net"}', $html);
    }

    public function testCreateWithUser() {
        $site = $this->getMockSite(null, ['url' => 'http://www.my-website.net']);
        $viewer = CMTest_TH::createUser();
        $googleAnalytics = new CMService_GoogleAnalytics_Client('key123');
        $environment = new CM_Frontend_Environment($site, $viewer);
        $request = new CM_Http_Request_Get('/pseudo-request');

        $html = $googleAnalytics->getHtml($environment);
        $this->assertContains('ga("create", "key123", {"cookieDomain":"www.my-website.net","userId":"' . $viewer->getId() . '"}',
            $html);
    }

    public function testGetMeasurementProtocolClient() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('key123');
        $measurementProtocolClient = $googleAnalytics->getMeasurementProtocolClient();

        $this->assertInstanceOf('CMService_GoogleAnalytics_MeasurementProtocol_Client', $measurementProtocolClient);
        $this->assertSame('key123', $measurementProtocolClient->getPropertyId());
    }

    public function testSetCustomMetric() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("set", "metric', $js);

        $googleAnalytics->setCustomMetric(2, 23.34);
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("set", "metric2", "23.34")', $js);
    }

    public function testSetCustomDimension() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("set", "dimension', $js);

        $googleAnalytics->setCustomDimension(3, 'foo');
        $googleAnalytics->setCustomDimension(4, '{"name":"müller"}');
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("set", "dimension3", "foo")', $js);
        $this->assertContains('ga("set", "dimension4", "{\"name\":\"m\u00fcller\"}")', $js);
    }

    public function testAddEvent() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('"hitType":"event"', $js);

        $googleAnalytics->addEvent('Sign Up', 'Click');
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("send", {"hitType":"event","eventCategory":"Sign Up","eventAction":"Click"});', $js);

        $googleAnalytics->addEvent('Subscription', 'Click', 'Label', 123, true);
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("send", {"hitType":"event","eventCategory":"Subscription","eventAction":"Click","eventLabel":"Label","eventValue":123,"nonInteraction":true});', $js);
    }

    public function testAddPageView() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("send", "pageview"', $js);

        $googleAnalytics->addPageView(' / foo');
        $js = $googleAnalytics->getJs();
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", " \/ foo");'));

        $googleAnalytics->addPageView(' / foo');
        $js = $googleAnalytics->getJs();
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview", " \/ foo");'));

        $googleAnalytics->addPageView(' / bar');
        $js = $googleAnalytics->getJs();
        $this->assertSame(3, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview", " \/ foo");'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", " \/ bar");'));
    }

    public function testSetPageView() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("send", "pageview"', $js);

        $googleAnalytics->addPageView('/foo');
        $js = $googleAnalytics->getJs();
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", "\/foo");'));

        $googleAnalytics->setPageView('/bar');
        $js = $googleAnalytics->getJs();
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", "\/bar");'));
    }

    public function testTrackPageViewSetsUser() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("set", "userId"', $js);

        $viewer = CMTest_TH::createUser();
        $environmentWithViewer = new CM_Frontend_Environment(null, $viewer);
        $googleAnalytics->trackPageView($environmentWithViewer, '/foo');
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("set", "userId", "' . $viewer->getId() . '")', $js);
    }

    public function testAddSale() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("require", "ecommerce")', $js);
        $this->assertNotContains('ecommerce:addTransaction', $js);
        $this->assertNotContains('ecommerce:addItem', $js);
        $this->assertNotContains('ecommerce:send', $js);

        $googleAnalytics->addSale('t123', 'p123', 1.23);
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("require", "ecommerce")', $js);
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":1.23});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);

        $googleAnalytics->addSale('t123', 'p456', 4.56);
        $js = $googleAnalytics->getJs();
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(2, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":5.79});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p456","sku":"p456","price":4.56,"quantity":1});', $js);

        $googleAnalytics->addSale('t789', 'p789', 7.89);
        $js = $googleAnalytics->getJs();
        $this->assertSame(2, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(3, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":5.79});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p456","sku":"p456","price":4.56,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t789","revenue":7.89});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t789","name":"product-p789","sku":"p789","price":7.89,"quantity":1});', $js);
    }

    public function testPushEvent() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $user);
        $this->forceInvokeMethod($googleAnalytics, '_pushHit', [$user, 'event', [
            'category'       => 'User',
            'action'         => 'Create',
            'label'          => 'foo',
            'value'          => 123,
            'nonInteraction' => true,
        ]]);
        $this->forceInvokeMethod($googleAnalytics, '_pushHit', [$user, 'pageview', ['path' => '/v/join/done']]);
        $js = $googleAnalytics->getJs();
        $this->assertSame('', $js);

        $googleAnalytics->trackPageView($environment, '/foo');
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("set", "userId", "' . $user->getId() . '");', $js);
        $this->assertContains('ga("send", "pageview", "\/foo");', $js);
        $this->assertContains('ga("send", "pageview", "\/v\/join\/done");', $js);
        $this->assertContains('ga("send", {"hitType":"event","eventCategory":"User","eventAction":"Create","eventLabel":"foo","eventValue":123,"nonInteraction":true});', $js);
    }

    public function testTtl() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('', 1);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $user);
        $this->forceInvokeMethod($googleAnalytics, '_pushHit', [$user, 'pageview', ['path' => '/v/join/done']]);
        $googleAnalytics->trackPageView($environment, '/foo');
        $js = $googleAnalytics->getJs();
        $this->assertContains('ga("send", "pageview", "\/v\/join\/done");', $js);
    }

    public function testTtlExpired() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('', 0);
        $user = CMTest_TH::createUser();
        $environment = new CM_Frontend_Environment(null, $user);
        $this->forceInvokeMethod($googleAnalytics, '_pushHit', [$user, 'pageview', ['path' => '/v/join/done']]);
        $googleAnalytics->trackPageView($environment, '/foo');
        $js = $googleAnalytics->getJs();
        $this->assertNotContains('ga("send", "pageview", "\/v\/join\/done");', $js);
    }

    public function testAddPlugin() {
        $ga = new CMService_GoogleAnalytics_Client('');
        $env = new CM_Frontend_Environment();
        $ga->addPlugin('Foo');
        $this->assertContains('ga("require", "Foo");', $ga->getHtml($env));
        $ga->addPlugin('Bar', 'tracker1');
        $this->assertContains('ga("require", "Foo");ga("tracker1.require", "Bar");', $ga->getHtml($env));
        $ga->addPlugin('Baz', null, ['foo' => true]);
        $this->assertContains('ga("require", "Foo");ga("tracker1.require", "Bar");ga("require", "Baz", {"foo":true});', $ga->getHtml($env));
        $ga->addPlugin('Boo', 'tracker2', ['foo' => true]);
        $this->assertContains('ga("require", "Foo");ga("tracker1.require", "Bar");ga("require", "Baz", {"foo":true});ga("tracker2.require", "Boo", {"foo":true});', $ga->getHtml($env));
    }
}
