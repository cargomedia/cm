<?php

class CM_Frontend_EnvironmentTest extends CMTest_TestCase {


    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetters() {
        $site = CM_Site_Abstract::factory();
        $user = CM_Model_User::createStatic();
        $language = CM_Model_Language::create('English', 'en', true);
        $timezone = new DateTimeZone('Europe/London');
        $debug = true;
        $location = CM_Model_Location::createCountry('United Kingdom', 'UK');
        $currency = CMTest_TH::createDefaultCurrency();
        $clientDevice = new CM_Http_ClientDevice(new CM_Http_Request_Get('/'));
        $environment = new CM_Frontend_Environment($site, $user, $language, $timezone, $debug, $location, $currency, $clientDevice);

        $this->assertSame($site, $environment->getSite());
        $this->assertSame($user, $environment->getViewer(true));
        $this->assertSame($language, $environment->getLanguage());
        $this->assertSame($language->getAbbreviation(), $environment->getLocale());
        $this->assertSame($timezone, $environment->getTimeZone());
        $this->assertSame($debug, $environment->isDebug());
        $this->assertSame($location, $environment->getLocation());
        $this->assertSame($currency, $environment->getCurrency());
        $this->assertSame($clientDevice, $environment->getClientDevice());
    }

    public function testSetNull() {
        $defaultCurrency = CM_Model_Currency::create('840', 'USD');
        $environment = new CM_Frontend_Environment();
        $this->assertEquals(CM_Site_Abstract::factory(), $environment->getSite());
        $this->assertNull($environment->getViewer());
        $this->assertNull($environment->getLanguage());
        $this->assertSame('en', $environment->getLocale());
        $this->assertEquals(CM_Bootloader::getInstance()->getTimeZone(), $environment->getTimeZone());
        $this->assertSame(CM_Bootloader::getInstance()->isDebug(), $environment->isDebug());
        $this->assertNull($environment->getLocation());
        $this->assertEquals($defaultCurrency, $environment->getCurrency());
        $this->assertNull($environment->getClientDevice());
    }

    /**
     * @expectedException CM_Exception_AuthRequired
     */
    public function testGetViewerNeeded() {
        $environment = new CM_Frontend_Environment();
        $environment->getViewer(true);
    }

    public function testHasViewer() {
        $environment = new CM_Frontend_Environment();
        $this->assertFalse($environment->hasViewer());
        $environment = new CM_Frontend_Environment(null, CM_Model_User::createStatic());
        $this->assertTrue($environment->hasViewer());
    }
}
