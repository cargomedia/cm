<?php

class CM_Stream_Adapter_Video_AbstractTest extends CMTest_TestCase {

	public function testGetSeverId() {
		CM_Config::get()->CM_Stream_Video->servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109',
			'privateIp' => '10.0.3.108'));

		/** @var $mockAdapter CM_Stream_Adapter_Video_Abstract */
		$mockAdapter = $this->getMockForAbstractClass('CM_Stream_Adapter_Video_Abstract');
		$servers = array('10.0.3.109' => 'dummyHost', '10.0.3.108' => 'dummyHost', 'dummyIp' => 'video.example.com');
		foreach ($servers as $ipAddresses => $host) {
			$request = $this->getMockForAbstractClass('CM_Request_Abstract', array($host), 'CM_Request_Mock', true, true, true, array('getIp', 'getHost'));
			$request->expects($this->any())->method('getIp')->will($this->returnValue(sprintf('%u', ip2long($ipAddresses))));
			$request->expects($this->any())->method('getHost')->will($this->returnValue($host));
			$this->assertEquals(1, $mockAdapter->getServerId($request));
		}
		try {
			$request = $this->getMockForAbstractClass('CM_Request_Abstract', array($host), 'CM_Request_Mock', true, true, true, array('getIp', 'getHost'));
			$request->expects($this->any())->method('getIp')->will($this->returnValue(sprintf('%u', ip2long('66.66.66.66'))));
			$request->expects($this->any())->method('getHost')->will($this->returnValue('not-existing-host'));
			$mockAdapter->getServerId($request);
			$this->fail('Found server with incorrect ipAddress and host');
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('No video server', $e->getMessage());
			$this->assertContains('`66.66.66.66`', $e->getMessage());
			$this->assertContains('`not-existing-host`', $e->getMessage());
		}
	}
}
