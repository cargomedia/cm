<?php

class CM_FormField_AbstractTest extends CMTest_TestCase {

	public function testFactory() {
		$className = 'CM_FormField_File';
		$field = CM_FormField_Abstract::factory($className);
		$this->assertInstanceOf('CM_FormField_Abstract', $field);
		$this->assertInstanceOf($className, $field);
	}

	public function testFactoryInvalid() {
		$className = 'file';
		try {
			$field = CM_FormField_Abstract::factory($className);
			$this->fail('should throw exception because invalid file');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}
}
