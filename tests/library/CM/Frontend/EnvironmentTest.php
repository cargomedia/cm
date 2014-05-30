<?php

class CM_Frontend_EnvironmentTest extends CMTest_TestCase {

    public function testGetters() {
        $site = CM_Site_Abstract::factory();
        $user = CM_Model_User::createStatic();
        $language = CM_Model_Language::create('English', 'en', true);
        $timezone = new DateTimeZone('Europe/London');
        $debug = true;
        $environment = new CM_Frontend_Environment($site, $user, $language, $timezone, $debug);

        $this->assertSame($site, $environment->getSite());
        $this->assertSame($user, $environment->getViewer(true));
        $this->assertSame($language, $environment->getLanguage());
        $this->assertSame($language->getAbbreviation(), $environment->getLocale());
        $this->assertSame($timezone, $environment->getTimeZone());
        $this->assertSame($debug, $environment->isDebug());
    }

    public function testHasViewer() {
        $environment = new CM_Frontend_Environment();
        $this->assertFalse($environment->hasViewer());
        $environment = new CM_Frontend_Environment(null, CM_Model_User::createStatic());
        $this->assertTrue($environment->hasViewer());
    }
}
