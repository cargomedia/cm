<?php

class CM_Response_PageTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testProcessLanguageRedirect() {
		CMTest_TH::createLanguage('en');
		$user = CMTest_TH::createUser();
		$response = CMTest_TH::createResponsePage('/en/mock5', null, $user);
		$response->process();
		$this->assertContains('Location: ' . $response->getSite()->getUrl() . '/mock5', $response->getHeaders());
	}

	public function testProcessLanguageNoRedirect() {
		$language = CMTest_TH::createLanguage('en');
		$user = CMTest_TH::createUser();
		$response = CMTest_TH::createResponsePage('/en/mock5');
		$response->process();
		$this->assertEquals($language, $response->getRequest()->getLanguageUrl());

		$response = CMTest_TH::createResponsePage('/mock5');
		$response->process();
		$this->assertNull($response->getRequest()->getLanguageUrl());

		$response = CMTest_TH::createResponsePage('/mock5', null, $user);
		$response->process();
		$this->assertNull($response->getRequest()->getLanguageUrl());
		foreach ($response->getHeaders() as $header) {
			$this->assertNotContains('Location:', $header);
		}
	}

	public function testProcessHostRedirect() {
		$site = CM_Site_Abstract::factory();
		$redirectHeader = 'Location: http://' . $site->getHost() . '/mock5';

		$response = CMTest_TH::createResponsePage('/mock5', array('host' => $site->getHost()));
		$response->process();
		$this->assertNotContains($redirectHeader, $response->getHeaders());

		$response = CMTest_TH::createResponsePage('/mock5', array('host' => 'incorrect-host.org'));
		$response->process();
		$this->assertContains($redirectHeader, $response->getHeaders());
	}
}

class CM_Page_Mock5 extends CM_Page_Abstract {

	public function getLayout() {
		$layoutname = 'Mock';
		$classname = self::_getClassNamespace() . '_Layout_' . $layoutname;
		return new $classname($this);
	}
}

class CM_Layout_Mock extends CM_Layout_Abstract {

}
