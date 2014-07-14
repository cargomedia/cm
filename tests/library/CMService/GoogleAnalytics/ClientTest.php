<?php

class CMService_GoogleAnalytics_ClientTest extends CMTest_TestCase {

    public function testAddPageView() {
        $getPageViews = CMTest_TH::getProtectedMethod('CMService_GoogleAnalytics_Client', '_getPageViews');
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $this->assertSame(array(), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView('/foo');
        $this->assertSame(array('/foo'), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView('/foo');
        $this->assertSame(array('/foo', '/foo'), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView('/bar');
        $this->assertSame(array('/foo', '/foo', '/bar'), $getPageViews->invoke($googleAnalytics));
    }

    public function testAddPageView_withoutPath() {
        $getPageViews = CMTest_TH::getProtectedMethod('CMService_GoogleAnalytics_Client', '_getPageViews');
        $googleAnalytics = new CMService_GoogleAnalytics_Client('');
        $this->assertSame(array(), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView();
        $this->assertSame(array(null), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView();
        $this->assertSame(array(null), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView('/foo');
        $this->assertSame(array('/foo'), $getPageViews->invoke($googleAnalytics));
        $googleAnalytics->addPageView(null);
        $this->assertSame(array('/foo'), $getPageViews->invoke($googleAnalytics));
    }
}
