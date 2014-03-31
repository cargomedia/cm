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
        CM_Config::get()->CM_Site_Abstract->types = array(12345 => 'CM_Site_Abstract_Mock12345');
        $this->assertEquals(array($site), CM_Site_Abstract::getAll());
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
        $siteClassMatchFoo = $this->getMockBuilder('CM_Site_Abstract')
            ->setMethods(array('getUrl'))
            ->setMockClassName('CM_Site_MockFoo')
            ->getMockForAbstractClass();
        $siteClassMatchFoo->expects($this->any())->method('getUrl')->will($this->returnValue('http://www.example.com'));
        /** @var CM_Site_Abstract $siteClassMatchFoo */

        $siteClassMatchBar = $this->getMockBuilder('CM_Site_Abstract')
            ->setMethods(array('getUrl'))
            ->setMockClassName('CM_Site_MockBar')
            ->getMockForAbstractClass();
        $siteClassMatchBar->expects($this->any())->method('getUrl')->will($this->returnValue('http://www.example.xxx'));
        /** @var CM_Site_Abstract $siteClassMatchBar */

        $requestCom = new CM_Request_Get('/', array('host' => 'www.example.com'));
        $this->assertTrue($siteClassMatchFoo->match($requestCom));

        $requestXxx = new CM_Request_Get('/', array('host' => 'www.example.xxx'));
        $this->assertTrue($siteClassMatchBar->match($requestXxx));

        $requestNot = new CM_Request_Get('/', array('host' => 'www.example.foo'));
        $this->assertFalse($siteClassMatchBar->match($requestNot));
    }

    public function testFactory() {
        try {
            CM_Site_Abstract::factory(9999);
            $this->fail('Factory returned non-configured site');
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertContains('Site with type `9999` not configured', $ex->getMessage());
        }
    }
}
