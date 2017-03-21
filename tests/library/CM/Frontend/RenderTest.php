<?php

class CM_Frontend_RenderTest extends CMTest_TestCase {

    protected function setUp() {
        CM_Config::get()->CM_Site_Abstract->url = 'http://www.default.dev';
        CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://cdn.default.dev';
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetSiteName() {
        $render = new CM_Frontend_Render();
        $this->assertSame('Default', $render->getSiteName());
    }

    public function testGetUrl() {
        $render = new CM_Frontend_Render();
        $this->assertSame('http://www.default.dev', $render->getUrl());
        $this->assertSame('http://www.default.dev/foo/bar', $render->getUrl('/foo/bar'));
        $this->assertSame('http://www.default.dev/0', $render->getUrl('/0'));

        $language = CM_Model_Language::create('Test language', 'test', true);
        $render->getEnvironment()->setLanguage($language);
        $this->assertSame('http://www.default.dev/foo/bar', $render->getUrl('/foo/bar'));

        $site = $this->getMockSite(null, null, array(
            'url'          => 'http://www.test.dev',
            'urlCdn'       => 'http://cdn.test.dev',
            'name'         => 'Test',
            'emailAddress' => 'test@test.dev',
        ));
        $this->assertSame('http://www.test.dev/foo/bar', $render->getUrl('/foo/bar', $site));
    }

    public function testGetUrlPage() {
        $render = new CM_Frontend_Render();
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Foo_Bar_FooBar', false);

        $this->assertSame('http://www.default.dev/foo/bar/foo-bar',
            $render->getUrlPage('CM_Page_Foo_Bar_FooBar'));
        $this->assertSame('http://www.default.dev/foo/bar/foo-bar',
            $render->getUrlPage($page));
        $this->assertSame('http://www.default.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $render->getUrlPage('CM_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar')));
        $this->assertSame('http://www.default.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $render->getUrlPage($page, array('userId' => 15, 'foo' => 'bar')));
    }

    public function testGetUrlPageInvalidModule() {
        $render = new CM_Frontend_Render();

        $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'INVALIDMODULE_Page_Test', false);
        try {
            $render->getUrlPage('INVALIDMODULE_Page_Test');
            $this->fail('Can compute path of page with invalid module');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertTrue(true);
        }
    }

    public function testGetUrlPageDifferentSite() {
        $render = new CM_Frontend_Render();
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Foo_Bar_FooBar', false);

        $site = $this->getMockSite(null, null, array(
            'url'          => 'http://www.test.dev',
            'urlCdn'       => 'http://cdn.test.dev',
            'name'         => 'Test',
            'emailAddress' => 'test@test.dev',
        ));
        $renderSite = new CM_Frontend_Render(new CM_Frontend_Environment($site));

        $this->assertSame('http://www.test.dev/foo/bar/foo-bar',
            $render->getUrlPage('CM_Page_Foo_Bar_FooBar', null, $site));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar',
            $render->getUrlPage($page, null, $site));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $render->getUrlPage('CM_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar'), $site));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $render->getUrlPage($page, array('userId' => 15, 'foo' => 'bar'), $site));

        $this->assertSame('http://www.test.dev/foo/bar/foo-bar',
            $renderSite->getUrlPage('CM_Page_Foo_Bar_FooBar'));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar',
            $renderSite->getUrlPage($page));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $renderSite->getUrlPage('CM_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar')));
        $this->assertSame('http://www.test.dev/foo/bar/foo-bar?userId=15&foo=bar',
            $renderSite->getUrlPage($page, array('userId' => 15, 'foo' => 'bar')));
    }

    public function testGetUrlPageDifferentSiteThrows() {
        $render = new CM_Frontend_Render();
        $page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'FOO_Page_Test123', false);

        /** @var CM_Exception_Invalid $exception */
        $exception = $this->catchException(function () use ($render, $page) {
            $render->getUrlPage($page);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Site does not contain namespace', $exception->getMessage());
        $this->assertSame(['site' => get_class($render->getEnvironment()->getSite()), 'namespace' => 'FOO'], $exception->getMetaInfo());

        $site = $this->getMockSite(null, null, array(
            'url'          => 'http://www.test.dev',
            'urlCdn'       => 'http://cdn.test.dev',
            'name'         => 'Test',
            'emailAddress' => 'test@test.dev',
        ), ['getModules']);
        $site->expects($this->any())->method('getModules')->willReturn(['CM', 'FOO']);

        $renderSite = new CM_Frontend_Render(new CM_Frontend_Environment($site));

        $this->assertSame('http://www.test.dev/test123', $renderSite->getUrlPage($page));
        $this->assertSame('http://www.test.dev/test123', $render->getUrlPage($page, null, $site));
    }

    public function testGetUrlResource() {
        $render = new CM_Frontend_Render();
        $siteType = (new CM_Site_SiteFactory())->getDefaultSite()->getType();
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $this->assertSame(
            'http://cdn.default.dev/layout/' . $siteType . '/' . $deployVersion . '/foo/bar.jpg', $render->getUrlResource('layout', 'foo/bar.jpg'));
        $this->assertSame('http://cdn.default.dev/layout/' . $siteType . '/' . $deployVersion . '/0', $render->getUrlResource('layout', '0'));
        $this->assertSame('http://cdn.default.dev/0/' . $siteType . '/' . $deployVersion . '/foo.jpg', $render->getUrlResource('0', 'foo.jpg'));
    }

    public function testGetUrlResourceDifferentSite() {
        $render = new CM_Frontend_Render();
        $site = $this->getMockSite('CM_Site_Abstract', null, ['urlCdn' => 'http://cdn.other.com']);
        $siteType = $site->getType();
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $this->assertSame('http://cdn.other.com/layout/' . $siteType . '/' . $deployVersion . '/foo/bar.jpg',
            $render->getUrlResource('layout', 'foo/bar.jpg', $site));
    }

    public function testGetUrlStatic() {
        $render = new CM_Frontend_Render();
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $this->assertSame('http://cdn.default.dev/static', $render->getUrlStatic());
        $this->assertSame('http://cdn.default.dev/static/foo.jpg?' . $deployVersion, $render->getUrlStatic('/foo.jpg'));
        $this->assertSame('http://cdn.default.dev/static/0?' . $deployVersion, $render->getUrlStatic('/0'));
    }

    public function testGetUrlStaticDifferentSite() {
        $render = new CM_Frontend_Render();
        $site = $this->getMockSite('CM_Site_Abstract', null, ['urlCdn' => 'http://cdn.other.com']);
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $this->assertSame('http://cdn.other.com/static/foo.jpg?' . $deployVersion, $render->getUrlStatic('/foo.jpg', $site));
    }

    public function testGetTranslation() {
        $render = new CM_Frontend_Render();
        $this->assertSame('abc', $render->getTranslation('abc'));
        $this->assertSame('abc cool', $render->getTranslation('abc {$variable}', array('variable' => 'cool')));
        $this->assertSame('abc ', $render->getTranslation('abc {$variable}'));
        $this->assertSame('abc ', $render->getTranslation('abc {$variable}', array('foo' => 'bar')));

        $language = CM_Model_Language::create('Test language', 'test', true);
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, null, $language));
        $language->setTranslation('abc {$variable}', 'translated stuff is {$variable}');
        $this->assertSame('translated stuff is cool', $render->getTranslation('abc {$variable}', array('variable' => 'cool')));
    }

    public function testGetViewer() {
        $viewer = CMTest_TH::createUser();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, $viewer));
        $this->assertEquals($viewer, $render->getViewer());

        $render = new CM_Frontend_Render();
        $this->assertNull($render->getViewer());
    }

    public function testParseTemplateContent() {
        $viewer = CM_Model_User::createStatic();
        $render = new CM_Frontend_Render();
        $render->getEnvironment()->setViewer($viewer);

        $content = '{$viewer->getId()} {$foo} normal-text';
        $expected = $viewer->getId() . ' bar normal-text';
        $this->assertSame($expected, $render->parseTemplateContent($content, ['foo' => 'bar']));
    }

    public function testGetFormatterDate() {
        $time = new DateTime('2016-05-21 00:00:00', new DateTimeZone('UTC'));

        $timeZone = new DateTimeZone('Europe/Zurich');
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, null, null, $timeZone));
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        if (CMTest_TH::getVersionICU() < 50) {
            $this->assertSame('5/21/16 2:00 AM', $formatter->format($time));
        } else {
            $this->assertSame('5/21/16, 2:00 AM', $formatter->format($time));
        }
    }

    public function testGetFormatterDateNumericalTimeZone() {
        $time = new DateTime('2016-05-21 00:00:00', new DateTimeZone('UTC'));

        $timeZone = DateTime::createFromFormat('O', '+02:00')->getTimezone();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, null, null, $timeZone));
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        if (CMTest_TH::getVersionICU() < 50) {
            $this->assertSame('5/21/16 2:00 AM', $formatter->format($time));
        } else {
            $this->assertSame('5/21/16, 2:00 AM', $formatter->format($time));
        }
    }

    public function testGetFormatterDateNumericalOverrideTimeZone() {
        $time = new DateTime('2016-05-21 00:00:00', new DateTimeZone('UTC'));

        $timeZone = DateTime::createFromFormat('O', '+02:00')->getTimezone();
        $timeZoneOverride = DateTime::createFromFormat('O', '+03:00')->getTimezone();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment(null, null, null, $timeZone));
        $formatter = $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, null, $timeZoneOverride);
        if (CMTest_TH::getVersionICU() < 50) {
            $this->assertSame('5/21/16 3:00 AM', $formatter->format($time));
        } else {
            $this->assertSame('5/21/16, 3:00 AM', $formatter->format($time));
        }
    }

    public function testGetFormatterDateException() {
        $timezoneNameList = \Functional\reject(DateTimeZone::listIdentifiers(), function ($timeZoneName) {
            return IntlTimeZone::fromDateTimeZone(new DateTimeZone($timeZoneName));
        });
        if (empty($timezoneNameList)) {
            $this->markTestSkipped('No unsupported timezones');
        }

        try {
            $timeZoneName = \Functional\first($timezoneNameList);
            $render = new CM_Frontend_Render();
            $render->getFormatterDate(IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, null, new DateTimeZone($timeZoneName));
            $this->fail('Date formatter created with unsupported timezone');
        } catch (CM_Exception $ex) {
            $this->assertSame('Cannot create date formatter', $ex->getMessage());
        }
    }

    public function testGetLayoutPath() {
        $render = new CM_Frontend_Render();
        $this->assertSame(
            'layout/default/resource/img/favicon.svg',
            $render->getLayoutPath('resource/img/favicon.svg')
        );
    }
}
