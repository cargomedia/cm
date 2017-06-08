<?php

use CM\Url\Url;

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

        $siteFactory = new CM_Site_SiteFactory($unsorted);

        $reflection = new ReflectionClass($siteFactory);
        $property = $reflection->getProperty('_siteList');
        $property->setAccessible(true);
        $this->assertEquals($sorted, $property->getValue($siteFactory));
    }

    public function testFindGetSiteByUrl() {
        $site1 = $this->getMockSite(null, null, ['url' => 'http://my-site.com']);
        $site2 = $this->getMockSite(null, null, ['url' => 'http://my-site.com/hello']);
        $site3 = $this->getMockSite(null, null, ['url' => 'http://your-site.com', 'urlCdn' => 'http://cdn.your-site.com']);

        $siteList = [$site1, $site2, $site3];
        $siteFactory = new CM_Site_SiteFactory($siteList);

        $this->assertEquals($site1, $siteFactory->findSiteByUrl(new Url('http://my-site.com')));
        $this->assertEquals($site2, $siteFactory->findSiteByUrl(new Url('https://my-site.com/hello')));
        $this->assertEquals($site3, $siteFactory->findSiteByUrl(new Url('http://your-site.com/foo/bar?query')));
        $this->assertEquals($site3, $siteFactory->findSiteByUrl(new Url('http://cdn.your-site.com')));
        $this->assertNull($siteFactory->findSiteByUrl(new Url('http://their-site.com')));
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
