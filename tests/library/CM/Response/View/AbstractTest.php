<?php

class CM_Response_View_AbstractTest extends CMTest_TestCase {

	public function testLoadPageRedirectExternal() {
		$response = $this->getMockAjaxResponse('loadPage', 'CM_View_Abstract', array('path' => '/view/ajax/test/mock'));
		$this->assertAjaxResponseSuccess($response, array('redirectExternal' => 'http://www.foo.bar'));
	}
}

class CM_Page_View_Ajax_Test_Mock extends CM_Page_Abstract {

	public function prepareResponse(CM_Response_Page $response) {
		$response->redirectUrl('http://www.foo.bar');
	}
}

