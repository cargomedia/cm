<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_RenderTest extends TestCase {

	/**
	 * @var CM_Render $_render
	 */
	private static $_render = null;

	public static function setUpBeforeClass() {
		CM_Config::get()->CM_Site_Abstract = new stdClass();
		CM_Config::get()->CM_Site_Abstract->url = 'http://www.foo.com/';
		CM_Config::get()->CM_Site_Abstract->urlCdn = 'http://www.cdn.com/';
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function setup() {
		CM_Config::get()->CM_Render->cdnResource = false;
		CM_Config::get()->CM_Render->cdnUserContent = false;
	}

	public function testGetUrl() {
		$render = $this->getRender();
		$this->assertSame('http://www.foo.com/', $render->getUrl());
		$this->assertSame('http://www.cdn.com/', $render->getUrl(null, true));
		$this->assertSame('http://www.foo.com/foo/bar', $render->getUrl('foo/bar'));
		$this->assertSame('http://www.cdn.com/foo/bar', $render->getUrl('foo/bar', true));
	}

	public function testGetUrlPage() {
		$render = $this->getRender();
		$this->assertSame('/page', $render->getUrlPage('page'));
		$this->assertSame('/page?userId=15&foo=bar', $render->getUrlPage('page', array('userId' => 15, 'foo' => 'bar')));
		$this->assertSame('http://www.foo.com/page?userId=15&foo=bar', $render->getUrlPage('page', array('userId' => 15, 'foo' => 'bar'), true));
	}

	public function testGetUrlResource() {
		$render = $this->getRender();
		$this->assertSame('http://www.foo.com/', $render->getUrlResource());
		$this->assertSame('http://www.foo.com/', $render->getUrlResource('img'));
		$this->assertSame('http://www.foo.com/', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame(
			'http://www.foo.com/img/1/' . CM_App::getInstance()->getReleaseStamp() . '/foo/bar.jpg', $render->getUrlResource('img', 'foo/bar.jpg'));
		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource());
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource('img'));
		$this->assertSame('http://www.cdn.com/', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame(
			'http://www.cdn.com/img/1/' . CM_App::getInstance()->getReleaseStamp() . '/foo/bar.jpg', $render->getUrlResource('img', 'foo/bar.jpg'));
	}

	public function testGetUrlStatic() {
		$render = $this->getRender();
		$this->assertSame('http://www.foo.com/static/', $render->getUrlStatic());
		$this->assertSame('http://www.foo.com/static/foo.jpg?' . CM_App::getInstance()->getReleaseStamp(), $render->getUrlStatic('foo.jpg'));
		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://www.cdn.com/static/', $render->getUrlStatic());
		$this->assertSame('http://www.cdn.com/static/foo.jpg?' . CM_App::getInstance()->getReleaseStamp(), $render->getUrlStatic('foo.jpg'));
	}

	public function testGetUrlUserContent() {
		$render = $this->getRender();
		$userFile = $this->getMock('CM_File_UserContent', array('getPathRelative'), array(), '', false);
		$userFile->expects($this->any())->method('getPathRelative')->will($this->returnValue('foo/bar.jpg'));
		$this->assertSame('http://www.foo.com/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
		CM_Config::get()->CM_Render->cdnUserContent = true;
		$this->assertSame('http://www.cdn.com/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
	}

	private function getRender() {
		$siteMock = $this->getMockForAbstractClass('CM_Site_Abstract', array(), '', true, true, true, array('getId'));
		$siteMock->expects($this->any())->method('getId')->will($this->returnValue(1));
		return new CM_Render($siteMock);
	}
}
