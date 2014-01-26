<?php

class CM_Config_NodeTest extends CMTest_TestCase {

	public function testNodeClass() {
		$reflection = new ReflectionClass('CM_Config_Node');
		$this->assertEmpty($reflection->getProperties());
	}
}
