<?php

class CM_Class_AbstractTest extends CMTest_TestCase {

    /** @var boolean */
    private static $_configCacheFlagBackup;

    /** @var ReflectionProperty */
    private static $_configCacheFlag;

    public static function setupBeforeClass() {
        $reflectedClass = new ReflectionClass('CM_Class_Abstract');
        self::$_configCacheFlag = $reflectedClass->getProperty('_classConfigCacheEnabled');
        self::$_configCacheFlag->setAccessible(true);
        self::$_configCacheFlagBackup = self::$_configCacheFlag->getValue();
        self::$_configCacheFlag->setValue(true);
        CM_Config::get()->CM_Class_AbstractMock = new stdClass();
        CM_Config::get()->CM_Class_AbstractMock->types[CM_Class_Implementation::getTypeStatic()] = 'CM_Class_Implementation';
    }

    public static function tearDownAfterClass() {
        self::$_configCacheFlag->setValue(self::$_configCacheFlagBackup);
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

    public function testGetConfigCaching() {
        $reflectedClass = new ReflectionClass('CM_Class_Abstract');
        $configCacheList = $reflectedClass->getProperty('_classConfigList');
        $configCacheList->setAccessible(true);

        CM_Config::get()->CM_Class_Implementation = new stdClass();
        CM_Config::get()->CM_Class_Implementation->foo = 'foo';
        $this->assertSame('foo', CM_Class_Implementation::getConfig()->foo);
        CM_Config::get()->CM_Class_Implementation->foo = 'bar';
        $this->assertSame('foo', CM_Class_Implementation::getConfig()->foo);

        $configCacheList->setValue([]);
        $this->assertSame('foo', CM_Class_Implementation::getConfig()->foo);

        $cache = new CM_Cache_Storage_Apc();
        $cache->flush();
        $this->assertSame('foo', CM_Class_Implementation::getConfig()->foo);

        $configCacheList->setValue([]);
        $this->assertSame('bar', CM_Class_Implementation::getConfig()->foo);
    }

    public function testGetClassName() {
        $className = CM_Class_AbstractMock::getClassName(1);
        $this->assertEquals('CM_Class_Implementation', $className);

        try {
            $className = CM_Class_AbstractMock::getClassName(2);
            $this->fail('Classname defined.');
        } catch (CM_Class_Exception_TypeNotConfiguredException $ex) {
            $this->assertTrue(true);
        }
    }
}

class CM_Class_AbstractMockWithoutConfig extends CM_Class_Abstract {

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

    public static function getTypeStatic() {
        return 1;
    }
}
