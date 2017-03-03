<?php

class CM_Site_SiteFactoryTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSiteListSorted() {
        $ordering = '[(W7oZtOhV1KJ^znaCr@9+k/=cYipF3.&`m5NfEDxGLwjBS4?uT]PU;s<2e#Ib%v>RqX8!_dl6:-)"AM*$Qgy0,H';
        $sorted = [];
        $unsorted = [];
        foreach (str_split($ordering) as $index => $char) {
            $repeatedStr = str_repeat('a', mb_strlen($ordering) - $index);
            $unsorted[ord($char)] = $sorted[] = $this->getMockSite(null, null, ['url' => 'http://your-site-' . $repeatedStr . '.com']);
        }
        ksort($unsorted);

        $start = microtime(true);
        $siteFactory = new CM_Site_SiteFactory($unsorted);
        $end = microtime(true);
        printf('testSiteListSorted sorting duration: %sms', round(($end - $start) * 1000, 2));

        $reflection = new ReflectionClass($siteFactory);
        $property = $reflection->getProperty('_siteList');
        $property->setAccessible(true);
        $this->assertEquals($sorted, $property->getValue($siteFactory));
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

    public function testFindGetSiteById() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, null, ['url' => 'http://your-site.com']);

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

    public function testGetDefaultSite() {
        $config = CM_Config::get();
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, null, ['url' => 'http://your-site.com']);

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);
        $config->CM_Site_Abstract->class = null;
        $exception = $this->catchException(function () use ($siteFactory) {
            $siteFactory->getDefaultSite();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Default site is not set', $exception->getMessage());
        $config->CM_Site_Abstract->class = get_class($site2);
        $this->assertEquals($site2, $siteFactory->getDefaultSite());
    }

}
