<?php

class CM_Class_ConfigTraitTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
        $configCacheList = new ReflectionProperty('CM_ClassMockConfig', '_classConfigList');
        $configCacheList->setAccessible(true);
        $configCacheList->setValue([]);
        $configCacheFlag = new ReflectionProperty('CM_ClassMockConfig', '_classConfigCacheEnabled');
        $configCacheFlag->setAccessible(true);
        $configCacheFlag->setValue(false);
    }

    public function testGetConfig() {
        CM_Config::get()->CM_ClassMockConfig = new stdClass();
        CM_Config::get()->CM_ClassMockConfig->foo = 'bar';
        $mock = new CM_ClassMockConfig();

        $this->assertSame('bar', CMTest_TH::callProtectedMethod($mock, '_getConfig')->foo);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Class `CM_ClassMockConfig` has no configuration.
     */
    public function testGetConfigNoConfig() {
        $mock = new CM_ClassMockConfig();

        $this->assertSame('bar', CMTest_TH::callProtectedMethod($mock, '_getConfig')->foo);
    }

    public function testGetConfigCaching() {
        CM_Config::get()->classConfigCacheEnabled = true;
        $configCacheList = new ReflectionProperty('CM_ClassMockConfig', '_classConfigList');
        $configCacheList->setAccessible(true);
        $configCacheFlag = new ReflectionProperty('CM_ClassMockConfig', '_classConfigCacheEnabled');
        $configCacheFlag->setAccessible(true);
        $configCacheFlag->setValue(true);

        CM_Config::get()->CM_ClassMockConfig = new stdClass();
        CM_Config::get()->CM_ClassMockConfig->foo = 'foo';
        $this->assertSame('foo', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getConfig')->foo);
        CM_Config::get()->CM_ClassMockConfig->foo = 'bar';
        $this->assertSame('foo', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getConfig')->foo);

        $configCacheList->setValue([]);
        $this->assertSame('foo', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getConfig')->foo);

        $cache = new CM_Cache_Storage_Apc();
        $cache->flush();
        $this->assertSame('foo', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getConfig')->foo);

        $configCacheList->setValue([]);
        $this->assertSame('bar', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getConfig')->foo);
    }

    public function testGetClassName() {
        CM_Config::get()->CM_ClassMockConfig = new stdClass();
        CM_Config::get()->CM_ClassMockConfig->foo = 'bar';

        $this->assertSame('CM_ClassMockConfig', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getClassName'));

        CM_Config::get()->CM_ClassMockConfig->class = 'CM_ClassNameOverwritten';
        $this->assertSame('CM_ClassNameOverwritten', CMTest_TH::callProtectedMethod('CM_ClassMockConfig', '_getClassName'));
    }
}

class CM_ClassMockConfig {

    use CM_Class_ConfigTrait;
}
