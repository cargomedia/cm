<?php

class CM_Site_SiteFactoryTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testFindSite() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, null, ['url' => 'http://your-site.com']);

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);

        $this->assertEquals($site3, $siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'your-site.com'])));
        $this->assertEquals($site1, $siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'my-site.com'])));
        $this->assertEquals($site2, $siteFactory->findSite(new CM_Http_Request_Get('/hello/foo', ['host' => 'my-site.com'])));
        $this->assertNull($siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'another-site.com'])));
    }

}
