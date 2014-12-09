<?php

class CMService_GoogleAnalytics_ClientTest extends CMTest_TestCase {

    public function testAccount() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('key');
        $environment = new CM_Frontend_Environment();
        $html = $googleAnalytics->getHtml($environment);
        $this->assertContains("_gaq.push(['_setAccount', 'key']);", $html);
    }

    public function testAddCustomVar() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains("_gaq.push(['_setCustomVar'", $js);

        $googleAnalytics->addCustomVar(2, 'premium', 'yes', 1);
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains("_gaq.push(['_setCustomVar', 2, 'premium', 'yes', 1]);", $js);
    }

    public function testAddEvent() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains("_gaq.push(['_trackEvent'", $js);

        $googleAnalytics->addEvent('Sign Up', 'Click');
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains("_gaq.push(['_trackEvent', 'Sign Up', 'Click', undefined, undefined, undefined]);", $js);

        $googleAnalytics->addEvent('Subscription', 'Click', 'Label', 123, true);
        $js = $googleAnalytics->getJs($environment);
        $this->assertContains("_gaq.push(['_trackEvent', 'Sign Up', 'Click', undefined, undefined, undefined]);", $js);
        $this->assertContains("_gaq.push(['_trackEvent', 'Subscription', 'Click', 'Label', 123, true]);", $js);
    }

    public function testAddPageView() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains("_gaq.push(['_trackPageview'", $js);

        $googleAnalytics->addPageView('/foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview', '/foo']);"));

        $googleAnalytics->addPageView('/foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(2, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(2, substr_count($js, "_gaq.push(['_trackPageview', '/foo']);"));

        $googleAnalytics->addPageView('/bar');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(3, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(2, substr_count($js, "_gaq.push(['_trackPageview', '/foo']);"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview', '/bar']);"));
    }

    public function testAddPageView_withoutPath() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains("_gaq.push(['_trackPageview'", $js);

        $googleAnalytics->addPageView();
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview']);"));

        $googleAnalytics->addPageView();
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview']);"));

        $googleAnalytics->addPageView('/foo');
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview', '/foo']);"));

        $googleAnalytics->addPageView();
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackPageview', '/foo']);"));
    }

    public function testAddSale() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $js = $googleAnalytics->getJs($environment);
        $this->assertNotContains("_gaq.push(['_addTrans'", $js);
        $this->assertNotContains("_gaq.push(['_addItem'", $js);
        $this->assertNotContains("_gaq.push(['_trackTrans']);", $js);

        $googleAnalytics->addSale('t123', 'p123', 1.23);
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_addTrans'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_addItem'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackTrans']);"));
        $this->assertContains("_gaq.push(['_addTrans', 't123', '', '1.23', '', '', '', '', '']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't123', 'p123', 'product-p123', '', '1.23', '1']);", $js);

        $googleAnalytics->addSale('t123', 'p456', 4.56);
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(1, substr_count($js, "_gaq.push(['_addTrans'"));
        $this->assertSame(2, substr_count($js, "_gaq.push(['_addItem'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackTrans']);"));
        $this->assertContains("_gaq.push(['_addTrans', 't123', '', '5.79', '', '', '', '', '']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't123', 'p123', 'product-p123', '', '1.23', '1']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't123', 'p456', 'product-p456', '', '4.56', '1']);", $js);

        $googleAnalytics->addSale('t789', 'p789', 7.89);
        $js = $googleAnalytics->getJs($environment);
        $this->assertSame(2, substr_count($js, "_gaq.push(['_addTrans'"));
        $this->assertSame(3, substr_count($js, "_gaq.push(['_addItem'"));
        $this->assertSame(1, substr_count($js, "_gaq.push(['_trackTrans']);"));
        $this->assertContains("_gaq.push(['_addTrans', 't123', '', '5.79', '', '', '', '', '']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't123', 'p123', 'product-p123', '', '1.23', '1']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't123', 'p456', 'product-p456', '', '4.56', '1']);", $js);
        $this->assertContains("_gaq.push(['_addTrans', 't789', '', '7.89', '', '', '', '', '']);", $js);
        $this->assertContains("_gaq.push(['_addItem', 't789', 'p789', 'product-p789', '', '7.89', '1']);", $js);
    }

    public function testDomainName() {
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $environment = new CM_Frontend_Environment();
        $html = $googleAnalytics->getHtml($environment);
        $this->assertContains("_gaq.push(['_setDomainName', 'www.default.dev']);", $html);
    }
}
