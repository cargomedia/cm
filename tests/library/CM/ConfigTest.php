<?php

require_once __DIR__ . '/../../TestCase.php';

class CM_ConfigTest extends TestCase {

	public function testConstruct() {
		$config = CM_Config::get();
		$this->assertInstanceOf('stdClass', $config);
	}
}