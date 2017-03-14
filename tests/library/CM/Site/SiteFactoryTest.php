<?php

class CM_Site_SiteFactoryTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testFindSite() {
        $site1 = $this->getMockSite(null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, ['url' => 'http://your-site.com']);

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);

        $this->assertEquals($site3, $siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'your-site.com'])));
        $this->assertEquals($site1, $siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'my-site.com'])));
        $this->assertEquals($site2, $siteFactory->findSite(new CM_Http_Request_Get('/hello/foo', ['host' => 'my-site.com'])));
        $this->assertNull($siteFactory->findSite(new CM_Http_Request_Get('/foo', ['host' => 'another-site.com'])));
    }

    public function testFindGetSiteById() {
        $site1 = $this->getMockSite();
        $site2 = $this->getMockSite();
        $site3 = $this->getMockSite();

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);

        $this->assertEquals($site1, $siteFactory->findSiteById($site1->getId()));
        $this->assertEquals($site3, $siteFactory->findSiteById($site3->getId()));
        $this->assertNull($siteFactory->findSiteById(9999));

        $this->assertEquals($site2, $siteFactory->getSiteById($site2->getId()));
        $exception = $this->catchException(function () use ($siteFactory) {
            $siteFactory->getSiteById(9999);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site is not found', $exception->getMessage());
        $this->assertEquals($site3, $siteFactory->getSiteById($site3->getId()));
    }

    public function testFindGetSiteByType() {
        $site1 = $this->getMockSite();
        $site2 = $this->getMockSite();
        $site3 = $this->getMockSite();

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);

        $this->assertEquals($site1, $siteFactory->findSiteByType($site1->getType()));
        $this->assertEquals($site3, $siteFactory->findSiteByType($site3->getType()));
        $this->assertNull($siteFactory->findSiteByType(9999));

        $this->assertEquals($site2, $siteFactory->getSiteByType($site2->getType()));
        $exception = $this->catchException(function () use ($siteFactory) {
            $siteFactory->getSiteByType(9999);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site is not found', $exception->getMessage());
        $this->assertEquals($site3, $siteFactory->getSiteByType($site3->getType()));
    }

    public function testGetDefaultSite() {
        $site1 = $this->getMockSite(null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, ['url' => 'http://your-site.com']);

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);
        $exception = $this->catchException(function () use ($siteFactory) {
            $siteFactory->getDefaultSite();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Default site is not set', $exception->getMessage());
        $site2->setDefault(true);
        $this->assertEquals($site2, $siteFactory->getDefaultSite());
    }

}
