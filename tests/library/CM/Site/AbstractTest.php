<?php

class CM_Site_AbstractTest extends CMTest_TestCase {

    public static function setUpBeforeClass() {
        CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com';
        CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
        CM_Config::get()->CM_Site_Abstract->name = 'Foo';
        CM_Config::get()->CM_Site_Abstract->emailAddress = 'foo@foo.com';
    }

    public function testGetAll() {
        $site = $this->getMockSite('CM_Site_Abstract', 12345);
        CM_Config::get()->CM_Site_Abstract->types = array(12345 => get_class($site));
        $this->assertEquals(array($site), CM_Site_Abstract::getAll());
    }

    public function testGetConfig() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $config = CM_Config::get()->CM_Site_Abstract;
        $this->assertEquals($config, $site->getConfig());
    }

    public function testGetEmailAddress() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $this->assertEquals('foo@foo.com', $site->getEmailAddress());
    }

    public function testGetName() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $this->assertEquals('Foo', $site->getName());
    }

    public function testGetUrl() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $this->assertEquals('http://www.foo.com', $site->getUrl());
    }

    public function testGetUrlCdn() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $this->assertEquals('http://www.cdn.com', $site->getUrlCdn());
    }

    public function testGetWebFontLoaderConfig() {
        /** @var CM_Site_Abstract $site */
        $site = $this->getMockForAbstractClass('CM_Site_Abstract');
        $this->assertEquals(null, $site->getWebFontLoaderConfig());
    }

    public function testIsUrlMatch() {
        $site = $this->getMockSite(null, null, [
            'url'    => 'http://www.my-site.com',
            'urlCdn' => 'http://cdn.my-site.com',
        ]);

        $this->assertSame(true, $site->isUrlMatch('my-site.com', '/'));
        $this->assertSame(true, $site->isUrlMatch('my-site.com', '/foo'));
        $this->assertSame(true, $site->isUrlMatch('www.my-site.com', '/foo'));
        $this->assertSame(true, $site->isUrlMatch('cdn.my-site.com', '/foo'));
        $this->assertSame(false, $site->isUrlMatch('something.my-site.com', '/foo'));
        $this->assertSame(false, $site->isUrlMatch('something.com', '/foo'));
    }

    public function testIsUrlMatchWithPath() {
        $site = $this->getMockSite(null, null, [
            'url' => 'http://www.my-site.com/foo',
        ]);

        $this->assertSame(false, $site->isUrlMatch('my-site.com', '/'));
        $this->assertSame(true, $site->isUrlMatch('my-site.com', '/foo'));
        $this->assertSame(true, $site->isUrlMatch('my-site.com', '/foo/bar'));
        $this->assertSame(true, $site->isUrlMatch('www.my-site.com', '/foo'));
        $this->assertSame(false, $site->isUrlMatch('something.my-site.com', '/foo'));
    }

    public function testFactory() {
        try {
            CM_Site_Abstract::factory(9999);
            $this->fail('Factory returned non-configured site');
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertSame('Site with given type is not configured', $ex->getMessage());
            $this->assertSame(['siteType' => 9999], $ex->getMetaInfo());
        }
    }

    public function testEquals() {
        $siteFoo = $this->mockClass('CM_Site_Abstract');
        /** @var CM_Site_Abstract $siteFoo1 */
        $siteFoo1 = $siteFoo->newInstance();
        /** @var CM_Site_Abstract $siteFoo2 */
        $siteFoo2 = $siteFoo->newInstance();

        $siteBar = $this->mockClass('CM_Site_Abstract');
        /** @var CM_Site_Abstract $siteBar1 */
        $siteBar1 = $siteBar->newInstance();

        $this->assertSame(true, $siteFoo2->equals($siteFoo1));
        $this->assertSame(true, $siteFoo1->equals($siteFoo2));
        $this->assertSame(false, $siteFoo1->equals(null));

        $this->assertSame(false, $siteFoo1->equals($siteBar1));
        $this->assertSame(false, $siteBar1->equals($siteFoo1));
    }

    public function testEqualsDifferentUrl() {
        $siteClass = $this->mockClass('CM_Site_Abstract');

        /** @var CM_Site_Abstract|\Mocka\AbstractClassTrait $site1 */
        $site1 = $siteClass->newInstance();
        $site1->mockMethod('getUrl')->set('http://my-site1.com');

        /** @var CM_Site_Abstract|\Mocka\AbstractClassTrait $site2 */
        $site2 = $siteClass->newInstance();
        $site2->mockMethod('getUrl')->set('http://my-site2.com');

        $this->assertSame(false, $site1->equals($site2));
    }
}
