<?php

require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_ConfigTest extends TestCase {

	public function testConstruct() {
		$config = CM_Config::get();
		$this->assertInstanceOf('stdClass', $config);
	}
}