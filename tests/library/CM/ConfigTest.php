<?php

class CM_ConfigTest extends CMTest_TestCase {

	public function testConstruct() {
		$config = CM_Config::get();
		$this->assertInstanceOf('CM_Config_Note', $config);
	}
}
