<?php

class CMService_Amazon_S3Test extends CMTest_TestCase {

	public function testConstructor() {
		new CMService_Amazon_S3();
		$this->assertTrue(true);
	}
}
