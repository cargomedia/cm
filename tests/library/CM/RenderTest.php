<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_RenderTest extends TestCase {

	private static $_configBackup;

	public static function setUpBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com/';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com/';
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		CM_Config::set(self::$_configBackup);
	}

	public function setup() {
		CM_Config::get()->CM_Render->cdnResource = false;
		CM_Config::get()->CM_Render->cdnUserContent = false;
	}

	public function testGetUrl() {
		$render = $this->_getRender();
		$this->assertSame('http://www.foo.com/', $render->getUrl());
		$this->assertSame('http://www.cdn.com/', $render->getUrl(null, true));
		$this->assertSame('http://www.foo.com/foo/bar', $render->getUrl('foo/bar'));
		$this->assertSame('http://www.cdn.com/foo/bar', $render->getUrl('foo/bar', true));
		$this->assertSame('http://www.foo.com/0', $render->getUrl(0));
	}

	public function testGetUrlPage() {
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Foo_Bar_FooBar', false);
		$render = $this->_getRender();
		$this->assertSame('http://www.foo.com/foo/bar/foo-bar', $render->getUrlPage('Test_Page_Foo_Bar_FooBar'));
		$this->assertSame('http://www.foo.com/foo/bar/foo-bar?userId=15&foo=bar', $render->getUrlPage('Test_Page_Foo_Bar_FooBar', array('userId' => 15,
			'foo' => 'bar')));
		$this->assertSame('http://www.foo.com/foo/bar/foo-bar?userId=15&foo=bar', $render->getUrlPage('Test_Page_Foo_Bar_FooBar', array('userId' => 15,
			'foo' => 'bar')));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'Test_Page_Index', false);
		$this->assertSame('http://www.foo.com/', $render->getUrlPage('Test_Page_Index'));
		try {
			$render->getUrlPage('NonexistentPage');
			$this->fail('Can compute path of nonexistent page class');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		$this->getMockForAbstractClass('CM_Model_Abstract', array(), 'InvalidClass', false);
		try {
			$render->getUrlPage('InvalidClass');
			$this->fail('Can compute path of invalid class');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
		$this->getMockForAbstractClass('CM_Model_Abstract', array(), 'InvalidNamespace_Page_Test', false);
		try {
			$render->getUrlPage('InvalidNamespace_Page_Test');
			$this->fail('Can compute path of page with invalid namespace');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetUrlResource() {
		$render = $this->_getRender();
		$releaseStamp = CM_App::getInstance()->getReleaseStamp();
		$this->assertSame('http://www.foo.com/', $render->getUrlResource());
		$this->assertSame('http://www.foo.com/', $render->getUrlResource('img'));
		$this->assertSame('http://www.foo.com/', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame('http://www.foo.com/img/1/' . $releaseStamp . '/foo/bar.jpg', $render->getUrlResource('img', 'foo/bar.jpg'));
		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource());
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource('img'));
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame('http://www.cdn.com/img/1/' . $releaseStamp . '/foo/bar.jpg', $render->getUrlResource('img', 'foo/bar.jpg'));
		$this->assertSame('http://www.cdn.com/img/1/' . $releaseStamp . '/0', $render->getUrlResource('img', '0'));
		$this->assertSame('http://www.cdn.com/0/1/' . $releaseStamp . '/foo.jpg', $render->getUrlResource('0', 'foo.jpg'));
	}

	public function testGetUrlStatic() {
		$render = $this->_getRender();
		$releaseStamp = CM_App::getInstance()->getReleaseStamp();
		$this->assertSame('http://www.foo.com/static/', $render->getUrlStatic());
		$this->assertSame('http://www.foo.com/static/foo.jpg?' . $releaseStamp, $render->getUrlStatic('foo.jpg'));
		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://www.cdn.com/static/', $render->getUrlStatic());
		$this->assertSame('http://www.cdn.com/static/foo.jpg?' . $releaseStamp, $render->getUrlStatic('foo.jpg'));
		$this->assertSame('http://www.cdn.com/static/0?' . $releaseStamp, $render->getUrlStatic('0'));
	}

	public function testGetUrlUserContent() {
		$render = $this->_getRender();
		$userFile = $this->getMock('CM_File_UserContent', array('getPathRelative'), array(), '', false);
		$userFile->expects($this->any())->method('getPathRelative')->will($this->returnValue('foo/bar.jpg'));
		$this->assertSame('http://www.foo.com/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
		CM_Config::get()->CM_Render->cdnUserContent = true;
		$this->assertSame('http://www.cdn.com/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
	}
}
