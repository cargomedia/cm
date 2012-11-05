<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Response_AbstractTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testFactory() {
		$responses = array();
		$responses['/captcha'] = 'CM_Response_Captcha';
		$responses['/emailtracking'] = 'CM_Response_EmailTracking';
		$responses['/longpolling'] = 'CM_Response_Longpolling';
		$responses['/rpc'] = 'CM_Response_RPC';
		$responses['/upload'] = 'CM_Response_Upload';
		$responses['/css'] = 'CM_Response_Resource_CSS';
		$responses['/img'] = 'CM_Response_Resource_Img';
		$responses['/js'] = 'CM_Response_Resource_JS';
		$responses['/ajax'] = 'CM_Response_View_Ajax';
		$responses['/form'] = 'CM_Response_View_Form';
		$responses['/homepage'] = 'CM_Response_Page';

		foreach ($responses as $path => $expectedResponse) {
			$request = new CM_Request_Post($path . '/' . CM_Site_CM::TYPE . '/timestamp', null, '');
			$this->assertInstanceOf($expectedResponse, CM_Response_Abstract::factory($request));
		}
	}

	public function testSetCookie() {
		$request = new CM_Request_Post('/' . CM_Site_CM::TYPE . '/timestamp', null, '');
		$clientId = $request->getClientId();
		/** @var CM_Response_Abstract $response */
		$response = $this->getMock('CM_Response_Abstract', array('process', '_setCookie'), array($request));
		$response->expects($this->once())->method('_setCookie')->with('clientId', (string) $clientId);
		$response->sendHeaders();
	}

}
