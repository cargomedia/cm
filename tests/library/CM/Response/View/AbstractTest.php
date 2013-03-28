<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

	public function testLoadPage() {
		$response = $this->getResponseAjax('loadPage', 'CM_View_Abstract', array('path' => CM_Page_View_Ajax_Test_Mock::getPath()));
		$this->assertAjaxResponseSuccess($response);
		$responseContent = CM_Params::decode($response->getContent(), true);
		$this->assertArrayHasKey('js', $responseContent['success']['data']);
		$this->assertArrayHasKey('html', $responseContent['success']['data']);
		$this->assertArrayHasKey('autoId', $responseContent['success']['data']);
		$this->assertSame(array(), $responseContent['success']['data']['menuEntryHashList']);
		$this->assertSame('', $responseContent['success']['data']['title']);
		$this->assertSame($response->getRender()->getUrlPage('CM_Page_View_Ajax_Test_Mock'), $responseContent['success']['data']['url']);
		$this->assertSame('CM_Layout_Mock1', $responseContent['success']['data']['layoutClass']);
	}

	public function testLoadPageRedirectExternal() {
		$response = $this->getResponseAjax('loadPage', 'CM_View_Abstract', array('path' => CM_Page_View_Ajax_Test_MockRedirect::getPath()));
		$this->assertAjaxResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
	}

	protected function _getSite(array $namespaces = null, $url = null, $urlCdn = null, $name = null, $emailAddress = null) {
		if (null === $url) {
			$url = 'http://www.test.com/';
		}
		return parent::_getSite($namespaces, $url, $urlCdn, $name, $emailAddress);
	}
}

class CM_Page_View_Ajax_Test_MockRedirect extends CM_Page_Abstract {

	public function prepareResponse(CM_Response_Page $response) {
		$response->redirectUrl('http://www.foo.bar');
	}
}

class CM_Page_View_Ajax_Test_Mock extends CM_Page_Abstract {

	public function getLayout() {
		$layoutname = 'Mock1';
		$classname = self::_getClassNamespace() . '_Layout_' . $layoutname;
		return new $classname($this);
	}
}

class CM_Layout_Mock1 extends CM_Layout_Abstract {

}
