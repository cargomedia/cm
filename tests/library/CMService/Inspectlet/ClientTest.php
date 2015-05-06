<?php

class CMService_Inspectlet_ClientTest extends CMTest_TestCase {

    public function testCreate() {
        $inspectlet = new CMService_Inspectlet_Client(123);
        $environment = new CM_Frontend_Environment();

        $html = $inspectlet->getHtml($environment);
        $this->assertContains("__insp.push(['wid', 123]);", $html);
    }

    public function testCreateWithUser() {
        $inspectlet = new CMService_Inspectlet_Client(123);
        $viewer = CMTest_TH::createUser();
        $viewerId = $viewer->getId();
        $environment = new CM_Frontend_Environment(null, $viewer);

        $html = $inspectlet->getHtml($environment);
        $this->assertContains("__insp.push(['wid', 123]);", $html);
        $this->assertContains("__insp.push(['identify', 'user{$viewerId}']);", $html);
    }

    public function testTrackPageViewSetsUser() {
        $inspectlet = new CMService_Inspectlet_Client(123);
        $environment = new CM_Frontend_Environment();

        $js = $inspectlet->getJs($environment);
        $this->assertNotContains("__insp.push(['identify'", $js);

        $viewer = CMTest_TH::createUser();
        $viewerId = $viewer->getId();
        $environmentWithViewer = new CM_Frontend_Environment(null, $viewer);
        $inspectlet->trackPageView($environmentWithViewer, '/foo');

        $js = $inspectlet->getJs($environment);
        $this->assertContains("__insp.push(['identify', 'user{$viewerId}']);", $js);
    }
}
