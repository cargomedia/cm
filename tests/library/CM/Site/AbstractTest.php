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

    public function testMatch() {
        $siteClassMatchCom = $this->getMockBuilder('CM_Site_Abstract')
            ->setMethods(array('getUrl'))
            ->setMockClassName('CM_Site_MockFoo')
            ->getMockForAbstractClass();
        $siteClassMatchCom->expects($this->any())->method('getUrl')->will($this->returnValue('http://www.example.com'));
        /** @var CM_Site_Abstract $siteClassMatchCom */

        $siteClassMatchXxx = $this->getMockBuilder('CM_Site_Abstract')
            ->setMethods(array('getUrl'))
            ->setMockClassName('CM_Site_MockBar')
            ->getMockForAbstractClass();
        $siteClassMatchXxx->expects($this->any())->method('getUrl')->will($this->returnValue('http://www.example.xxx'));
        /** @var CM_Site_Abstract $siteClassMatchXxx */

        $requestCom = new CM_Http_Request_Get('/', array('host' => 'www.example.com'));
        $this->assertTrue($siteClassMatchCom->match($requestCom));

        $requestXxx = new CM_Http_Request_Get('/', array('host' => 'www.example.xxx'));
        $this->assertTrue($siteClassMatchXxx->match($requestXxx));

        $requestNot = new CM_Http_Request_Get('/', array('host' => 'www.example.foo'));
        $this->assertFalse($siteClassMatchXxx->match($requestNot));

        $requestNotPartial = new CM_Http_Request_Get('/', array('host' => 'www.example.xxx.com'));
        $this->assertFalse($siteClassMatchXxx->match($requestNotPartial));
    }

    public function testMatchCdn() {
        $siteClass = $this->getMockBuilder('CM_Site_Abstract')
            ->setMethods(array('getUrl'))
            ->setMethods(array('getUrlCdn'))
            ->getMockForAbstractClass();
        $siteClass->expects($this->any())->method('getUrl')->will($this->returnValue('http://www.example.com'));
        $siteClass->expects($this->any())->method('getUrlCdn')->will($this->returnValue('http://cdn.example.com'));
        /** @var CM_Site_Abstract $siteClass */

        $this->assertTrue($siteClass->match(new CM_Http_Request_Get('/', array('host' => 'cdn.example.com'))));
        $this->assertFalse($siteClass->match(new CM_Http_Request_Get('/', array('host' => 'www.google.com'))));
    }

    public function testFactory() {
        try {
            CM_Site_Abstract::factory(9999);
            $this->fail('Factory returned non-configured site');
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertContains('Site with type `9999` not configured', $ex->getMessage());
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
