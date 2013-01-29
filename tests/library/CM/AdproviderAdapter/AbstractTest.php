<?php

class CM_AdproviderAdapter_AbstractTest extends CMTest_TestCase {

	public function testFactory() {
		CM_Config::get()->CM_AdproviderAdapter_Abstract->class = 'CM_AdproviderAdapter_Openx';

		$this->assertInstanceOf('CM_AdproviderAdapter_Openx', CM_AdproviderAdapter_Abstract::factory());

		CMTest_TH::clearConfig();
	}
}
