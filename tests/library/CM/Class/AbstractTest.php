<?php

require_once dirname(__FILE__) . '/../../../TestCase.php';

class CM_Class_AbstractTest extends TestCase {
	private static $_configBackup;

	public static function setupBeforeClass() {
		self::$_configBackup = CM_Config::get();
		CM_Config::get()->CM_Class_AbstractMock = new stdClass();
		CM_Config::get()->CM_Class_AbstractMock->types[CM_Class_Implementation::TYPE] = 'CM_Class_Implementation';
		CM_Config::get()->CM_Class_AbstractMock->foo = 'bar';
	}

	public static function tearDownAfterClass() {
		CM_Config::set(self::$_configBackup);
		TH::clearEnv();
	}

	public function testGetConfig() {
		$this->assertEquals('bar', CM_Class_AbstractMock::getConfig()->foo);

		try {
			$config = CM_Class_AbstractMockWithoutConfig::getConfig();
			$this->fail('Config exists.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}

	}

	public function testGetClassName() {
		$className = CM_Class_AbstractMock::getClassName(1);
		$this->assertEquals('CM_Class_Implementation', $className);

		try {
			$className = CM_Class_AbstractMock::getClassName(2);
			$this->fail('Classname defined.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}
}

class CM_Class_AbstractMockWithoutConfig extends CM_Class_Abstract{

	public static function getConfig() {
		return self::_getConfig();
	}
}

class CM_Class_AbstractMock extends CM_Class_Abstract {

	public static function getClassName($type) {
		return self::_getClassName($type);
	}

	public static function getConfig() {
		return self::_getConfig();
	}

}

class CM_Class_Implementation extends CM_Class_AbstractMock {
	const TYPE = 1;
}
