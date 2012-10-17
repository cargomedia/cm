<?php

require_once __DIR__ . '/../../TestCase.php';

class CM_AdproviderTest extends TestCase {

	protected function tearDown() {
		TH::clearConfig();
	}

	public function testGetHtml() {
		CM_Config::get()->CM_Adprovider->enabled = true;
		CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Mock', 'zoneId' => 1),);
		$adprovider = new CM_Adprovider();

		$this->assertSame('{"zoneId":1}', $adprovider->getHtml('foo'));

		CM_Config::get()->CM_Adprovider->enabled = false;
		$this->assertSame('', $adprovider->getHtml('foo'));
	}

	public function testGetHtmlInvalidAdapter() {
		CM_Config::get()->CM_Adprovider->enabled = true;
		CM_Config::get()->CM_Adprovider->zones = array('foo' => array('adapter' => 'CM_AdproviderAdapter_Nonexistent'),);
		$adprovider = new CM_Adprovider();

		try {
			$adprovider->getHtml('foo');
			$this->fail('No exception for invalid ad adapter');
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('Invalid ad adapter', $e->getMessage());
		}
	}
}

class CM_AdproviderAdapter_Mock extends CM_AdproviderAdapter_Abstract {

	public function getHtml($zoneData) {
		return json_encode($zoneData);
	}
}
