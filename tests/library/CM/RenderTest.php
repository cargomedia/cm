<?php

class CM_RenderTest extends CMTest_TestCase {

	/** @var CM_Site_Abstract */
	private $_site;

	public function setUp() {
		$this->_site = $this->getMockSite(null, array('getNamespaces'));
		$this->_site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('CM', 'TEST')));
		CMTest_TH::configureSite($this->_site, 'http://www.default.dev', 'http://cdn.default.dev', 'Default', 'default@default.dev');

		CM_Config::get()->CM_Render->cdnResource = false;
		CM_Config::get()->CM_Render->cdnUserContent = false;
	}

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testGetSiteName() {
		$render = new CM_Render($this->_site);
		$this->assertSame('Default', $render->getSiteName());
	}

	public function testGetUrl() {
		$render = new CM_Render($this->_site);
		$this->assertSame('http://www.default.dev', $render->getUrl());
		$this->assertSame('http://cdn.default.dev', $render->getUrl(null, true));
		$this->assertSame('http://www.default.dev/foo/bar', $render->getUrl('/foo/bar'));
		$this->assertSame('http://cdn.default.dev/foo/bar', $render->getUrl('/foo/bar', true));
		$this->assertSame('http://www.default.dev/0', $render->getUrl('/0'));
		$this->assertSame('http://cdn.default.dev/0', $render->getUrl('/0', true));
	}

	public function testGetUrlPage() {
		$render = new CM_Render($this->_site);
		$page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'TEST_Page_Foo_Bar_FooBar', false);

		$this->assertSame('http://www.default.dev/foo/bar/foo-bar',
			$render->getUrlPage('TEST_Page_Foo_Bar_FooBar'));
		$this->assertSame('http://www.default.dev/foo/bar/foo-bar',
			$render->getUrlPage($page));
		$this->assertSame('http://www.default.dev/foo/bar/foo-bar?userId=15&foo=bar',
			$render->getUrlPage('TEST_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar')));
		$this->assertSame('http://www.default.dev/foo/bar/foo-bar?userId=15&foo=bar',
			$render->getUrlPage('TEST_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar')));


	}

	public function testGetUrlPageInvalidNamespace() {
		$render = new CM_Render($this->_site);

		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'INVALIDNAMESPACE_Page_Test', false);
		try {
			$render->getUrlPage('INVALIDNAMESPACE_Page_Test');
			$this->fail('Can compute path of page with invalid namespace');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetUrlPageDifferentSite() {
		$render = new CM_Render($this->_site);
		$page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'TEST_Page_Foo_Bar_FooBar', false);

		$site = $this->getMockSite(null, array('getNamespaces'));
		$site->expects($this->any())->method('getNamespaces')->will($this->returnValue(array('CM', 'TEST')));
		CMTest_TH::configureSite($site, 'http://www.test2.dev', 'http://cdn.test2.dev', 'Test2', 'default@test2.dev');

		$this->assertSame('http://www.test2.dev/foo/bar/foo-bar',
			$render->getUrlPage('TEST_Page_Foo_Bar_FooBar', null, $site));
		$this->assertSame('http://www.test2.dev/foo/bar/foo-bar?userId=15&foo=bar',
			$render->getUrlPage('TEST_Page_Foo_Bar_FooBar', array('userId' => 15, 'foo' => 'bar'), $site));

		$this->assertSame('http://www.default.dev/foo/bar/foo-bar?userId=15&foo=bar',
			$render->getUrlPage($page, array('userId' => 15, 'foo' => 'bar')));
	}

	public function testGetUrlPageLanguageRewrite() {
		$page = $this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Test', false);
		$baseUrl = 'http://www.default.dev';

		$render = new CM_Render($this->_site, null, null, null);
		$this->assertSame($baseUrl . '/test', $render->getUrlPage($page));
		$render = new CM_Render($this->_site, null, null, true); // This should never happen in application, but lets test it
		$this->assertSame($baseUrl . '/test', $render->getUrlPage($page));

		$language = CMTest_TH::createLanguage('en');

		$render = new CM_Render($this->_site, null, null, null);
		$this->assertSame($baseUrl . '/test', $render->getUrlPage($page));
		$render = new CM_Render($this->_site, null, null, true); // This should never happen in application, but lets test it
		$this->assertSame($baseUrl . '/en/test', $render->getUrlPage($page));

		$render = new CM_Render($this->_site, null, $language, null);
		$this->assertSame($baseUrl . '/test', $render->getUrlPage($page));
		$render = new CM_Render($this->_site, null, $language, true);
		$this->assertSame($baseUrl . '/en/test', $render->getUrlPage($page));
	}

	public function testGetUrlResource() {
		$site = $this->_site;
		$render = new CM_Render($site);
		$siteType = $site->getType();
		$releaseStamp = CM_App::getInstance()->getReleaseStamp();
		$this->assertSame('http://www.default.dev', $render->getUrlResource());
		$this->assertSame('http://www.default.dev', $render->getUrlResource('layout'));
		$this->assertSame('http://www.default.dev', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame(
			'http://www.default.dev/layout/' . $siteType . '/' . $releaseStamp . '/foo/bar.jpg', $render->getUrlResource('layout', 'foo/bar.jpg'));
		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://cdn.default.dev', $render->getUrlResource());
		$this->assertSame('http://cdn.default.dev', $render->getUrlResource('layout'));
		$this->assertSame('http://cdn.default.dev', $render->getUrlResource(null, 'foo/bar.jpg'));
		$this->assertSame(
			'http://cdn.default.dev/layout/' . $siteType . '/' . $releaseStamp . '/foo/bar.jpg', $render->getUrlResource('layout', 'foo/bar.jpg'));
		$this->assertSame('http://cdn.default.dev/layout/' . $siteType . '/' . $releaseStamp . '/0', $render->getUrlResource('layout', '0'));
		$this->assertSame('http://cdn.default.dev/0/' . $siteType . '/' . $releaseStamp . '/foo.jpg', $render->getUrlResource('0', 'foo.jpg'));
	}

	public function testGetUrlStatic() {
		$render = new CM_Render($this->_site);
		$releaseStamp = CM_App::getInstance()->getReleaseStamp();
		$this->assertSame('http://www.default.dev/static', $render->getUrlStatic());
		$this->assertSame('http://www.default.dev/static/foo.jpg?' . $releaseStamp, $render->getUrlStatic('/foo.jpg'));

		CM_Config::get()->CM_Render->cdnResource = true;
		$this->assertSame('http://cdn.default.dev/static', $render->getUrlStatic());
		$this->assertSame('http://cdn.default.dev/static/foo.jpg?' . $releaseStamp, $render->getUrlStatic('/foo.jpg'));
		$this->assertSame('http://cdn.default.dev/static/0?' . $releaseStamp, $render->getUrlStatic('/0'));
	}

	public function testGetUrlUserContent() {
		$render = new CM_Render($this->_site);
		$userFile = $this->getMock('CM_File_UserContent', array('getPathRelative'), array(), '', false);
		$userFile->expects($this->any())->method('getPathRelative')->will($this->returnValue('foo/bar.jpg'));
		$this->assertSame('http://www.default.dev/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
		CM_Config::get()->CM_Render->cdnUserContent = true;
		$this->assertSame('http://cdn.default.dev/userfiles/foo/bar.jpg', $render->getUrlUserContent($userFile));
	}

	public function testGetTranslation() {
		$render = new CM_Render($this->_site);
		$this->assertSame('abc', $render->getTranslation('abc'));
		$this->assertSame('abc cool', $render->getTranslation('abc {$variable}', array('variable' => 'cool')));
		$this->assertSame('abc ', $render->getTranslation('abc {$variable}'));
		$this->assertSame('abc ', $render->getTranslation('abc {$variable}', array('foo' => 'bar')));

		/** @var CM_Model_Language $language */
		$language = CM_Model_Language::create(array(
			'name'         => 'Test language',
			'abbreviation' => 'test',
			'enabled'      => true
		));
		$render = new CM_Render($this->_site, null, $language);
		$language->setTranslation('abc {$variable}', 'translated stuff is {$variable}');
		CM_Model_Language::flushCacheLocal();
		$this->assertSame('translated stuff is cool', $render->getTranslation('abc {$variable}', array('variable' => 'cool')));
	}

	public function testGetViewer() {
		$viewer = CMTest_TH::createUser();
		$render = new CM_Render($this->_site, $viewer);
		$this->assertEquals($viewer, $render->getViewer());

		$render = new CM_Render($this->_site);
		$this->assertNull($render->getViewer());
	}
}
