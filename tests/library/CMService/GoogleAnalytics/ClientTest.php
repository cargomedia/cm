<?php

class CMService_GoogleAnalytics_ClientTest extends CMTest_TestCase {

    public function testAccount() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('key');
        $environment = new CM_Frontend_Environment();
        $html = $googleAnalytics->getHtml($environment);
        $this->assertContains('ga("create", "key"', $html);
    }

    public function testSetCustomMetric() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('ga("set", "metric', $js);

        $googleAnalytics->setCustomMetric(2, 23.34);
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains('ga("set", "metric2", 23.34)', $js);
    }

    public function testSetCustomDimension() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('ga("set", "dimension', $js);

        $googleAnalytics->setCustomDimension(3, 'foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains('ga("set", "dimension3", "foo")', $js);
    }

    public function testAddEvent() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('"hitType":"event"', $js);

        $googleAnalytics->addEvent('Sign Up', 'Click');
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains('ga("send", {"hitType":"event","eventCategory":"Sign Up","eventAction":"Click"});', $js);

        $googleAnalytics->addEvent('Subscription', 'Click', 'Label', 123, true);
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains('ga("send", {"hitType":"event","eventCategory":"Subscription","eventAction":"Click","eventLabel":"Label","eventValue":123,"nonInteraction":true});', $js);
    }

    public function testAddPageView() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('ga("send", "pageview"', $js);

        $googleAnalytics->addPageView(' / foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", " / foo");'));

        $googleAnalytics->addPageView(' / foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview", " / foo");'));

        $googleAnalytics->addPageView(' / bar');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(3, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(2, substr_count($js, 'ga("send", "pageview", " / foo");'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", " / bar");'));
    }

    public function testSetPageView() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('ga("send", "pageview"', $js);

        $googleAnalytics->addPageView('/foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", "/foo");'));

        $googleAnalytics->setPageView('/bar');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview"'));
        $this->assertSame(1, substr_count($js, 'ga("send", "pageview", "/bar");'));
    }

    public function testAddSale() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains('ga("require", "ecommerce")', $js);
        $this->assertNotContains('ecommerce:addTransaction', $js);
        $this->assertNotContains('ecommerce:addItem', $js);
        $this->assertNotContains('ecommerce:send', $js);

        $googleAnalytics->addSale('t123', 'p123', 1.23);
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains('ga("require", "ecommerce")', $js);
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":1.23});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);

        $googleAnalytics->addSale('t123', 'p456', 4.56);
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(2, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":5.79});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p456","sku":"p456","price":4.56,"quantity":1});', $js);

        $googleAnalytics->addSale('t789', 'p789', 7.89);
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(2, substr_count($js, 'ga("ecommerce:addTransaction"'));
        $this->assertSame(3, substr_count($js, 'ga("ecommerce:addItem"'));
        $this->assertSame(1, substr_count($js, 'ga("ecommerce:send");'));
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t123","revenue":5.79});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p123","sku":"p123","price":1.23,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t123","name":"product-p456","sku":"p456","price":4.56,"quantity":1});', $js);
        $this->assertContains('ga("ecommerce:addTransaction", {"id":"t789","revenue":7.89});', $js);
        $this->assertContains('ga("ecommerce:addItem", {"id":"t789","name":"product-p789","sku":"p789","price":7.89,"quantity":1});', $js);
    }
}
