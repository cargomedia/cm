<?php

class CM_Response_AbstractTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
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

	public function testSetDeleteCookie() {
		$request = new CM_Request_Post('/foo/' . CM_Site_CM::TYPE);
		$response = CM_Response_Abstract::factory($request);
		$time = time();
		$timeString = date('D\, d\-M\-Y h:i:s e', $time);

		$response->setCookie('foo', 'bar', $time);
		$response->setCookie('bar', 'bad!=();');
		$headers = $response->getHeaders();
		$this->assertSame('Set-Cookie: foo=bar; Expires=' . $timeString . '; Path=/', $headers[0]);
		$this->assertSame('Set-Cookie: bar=bad%21%3D%28%29%3B; Path=/', $headers[1]);

		$response->deleteCookie('foo');
		$headers = $response->getHeaders();
		$this->assertSame('Set-Cookie: foo=; Expires=Wed, 31-Dec-1969 06:00:01 US/Central; Path=/', $headers[0]);
	}
}
