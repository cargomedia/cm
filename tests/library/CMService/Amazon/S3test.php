<?php
require_once __DIR__ . '/../../../TestCase.php';

class CMService_Amazon_S3Test extends TestCase {

	public function testConstructor(){
		$amazonS3 = new CMService_Amazon_S3();
		$this->assertTrue(true);
	}

}

