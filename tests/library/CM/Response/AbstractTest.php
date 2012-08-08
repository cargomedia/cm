<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Response_AbstractTest extends TestCase {

	public function tearDown() {
		TH::clearEnv();
	}

	public function testFactory() {
		$responses = array(
			'/captcha'       => 'CM_Response_Captcha',
			'/emailtracking' => 'CM_Response_EmailTracking',
			'/longpolling'   => 'CM_Response_Longpolling',
			'/rpc'           => 'CM_Response_RPC',
			'/upload'        => 'CM_Response_Upload',
			'/css'           => 'CM_Response_Resource_CSS',
			'/img'           => 'CM_Response_Resource_Img',
			'/js'            => 'CM_Response_Resource_JS',
			'/ajax'          => 'CM_Response_View_Ajax',
			'/form'          => 'CM_Response_View_Form',
			'/homepage'      => 'CM_Response_Page',
		);
		foreach ($responses as $path => $expectedResponse) {
			$request = new CM_Request_Post($path . '/1/timestamp', null, null, '');
			$this->assertInstanceOf($expectedResponse, CM_Response_Abstract::factory($request));
		}
	}

}
