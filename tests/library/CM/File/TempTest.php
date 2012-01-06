<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_File_TempTest extends TestCase {

	public static function setUpBeforeClass() {
	}
	
	public function testConstructorInvalid() {
		
		try {
			new CM_File_Temp(uniqid());
			$this->fail('should throw an exception because id does not exist');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}
}