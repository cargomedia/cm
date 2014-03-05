<?php

class CM_Site_AbstractTest extends CMTest_TestCase {

  public static function setUpBeforeClass() {
    CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com';
    CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com';
    CM_Config::get()->CM_Site_Abstract->name = 'Foo';
    CM_Config::get()->CM_Site_Abstract->emailAddress = 'foo@foo.com';
  }

  public function testGetAll() {
    $this->assertSame(array(), CM_Site_Abstract::getAll());
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

  public function testFindByRequest() {
    $siteClassMatchFoo = $this->getMockClass('CM_Site_Abstract', array('match'), array(), 'CM_Site_MockFoo');
    $siteClassMatchFoo::staticExpects($this->any())->method('match')->will($this->returnCallback(function (CM_Request_Abstract $request) {
      return '/foo' === $request->getPath();
    }));

    $siteClassMatchBar = $this->getMockClass('CM_Site_Abstract', array('match'), array(), 'CM_Site_MockBar');
    $siteClassMatchBar::staticExpects($this->any())->method('match')->will($this->returnCallback(function (CM_Request_Abstract $request) {
      return '/bar' === $request->getPath();
    }));

    $site = $this->getMockClass('CM_Site_Abstract', array('getClassChildren'));
    $site::staticExpects($this->any())->method('getClassChildren')->will($this->returnValue(array($siteClassMatchFoo, $siteClassMatchBar)));

    $this->assertInstanceOf($siteClassMatchFoo, $site::findByRequest(new CM_Request_Get('/foo')));
    $this->assertInstanceOf($siteClassMatchBar, $site::findByRequest(new CM_Request_Get('/bar')));
    $this->assertInstanceOf(get_class(CM_Site_Abstract::factory()), $site::findByRequest(new CM_Request_Get('/somethingelse')));
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

    $site = $this->getMockClass('CM_Site_Abstract', array('getClassChildren'), array(), 'CM_Site_Mock');
    $site::staticExpects($this->any())->method('getClassChildren')->will($this->returnValue(array($siteClassMatchFoo, $siteClassMatchBar)));

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
