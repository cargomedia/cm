<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_AdproviderAdapter_AbstractTest extends TestCase {

	public function testFactory() {
		CM_Config::get()->CM_AdproviderAdapter_Abstract->class = 'CM_AdproviderAdapter_Openx';

		$this->assertInstanceOf('CM_AdproviderAdapter_Openx', CM_AdproviderAdapter_Abstract::factory());

		TH::clearConfig();
	}
}
