<?php

class CM_Class_AbstractTest extends CMTest_TestCase {

	public static function setupBeforeClass() {
		CM_Config::get()->CM_Class_AbstractMock = new stdClass();
		CM_Config::get()->CM_Class_AbstractMock->types[CM_Class_Implementation::TYPE] = 'CM_Class_Implementation';
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testGetConfig() {
		CM_Config::get()->CM_Class_AbstractMock->foo = 'foo';
		CM_Config::get()->CM_Class_AbstractMock->foobar = 'foo';
		CM_Config::get()->CM_Class_Implementation = new stdClass();
		CM_Config::get()->CM_Class_Implementation->bar = 'bar';
		CM_Config::get()->CM_Class_Implementation->foobar = 'bar';

		$this->assertEquals('foo', CM_Class_Implementation::getConfig()->foo);
		$this->assertEquals('bar', CM_Class_Implementation::getConfig()->bar);
		$this->assertEquals('bar', CM_Class_Implementation::getConfig()->foobar);

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
