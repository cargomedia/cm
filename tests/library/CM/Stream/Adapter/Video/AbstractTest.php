<?php

class CM_Stream_Adapter_Video_AbstractTest extends CMTest_TestCase {

	public function testGetSeverId() {
		CM_Config::get()->CM_Stream_Video->servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109',
			'privateIp' => '10.0.3.108'));

		/** @var $mockAdapter CM_Stream_Adapter_Video_Abstract */
		$mockAdapter = $this->getMockForAbstractClass('CM_Stream_Adapter_Video_Abstract');
		$hosts = array('10.0.3.109', '10.0.3.108', 'video.example.com');
		foreach ($hosts as $host) {
			$this->assertEquals(1, $mockAdapter->getServerId($host));
		}
		try {
			$mockAdapter->getServerId('66.66.66.66');
			$this->fail('Found server with ip 66.66.66.66');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('No video server with host `66.66.66.66` found', $ex->getMessage());
		}
	}
}
