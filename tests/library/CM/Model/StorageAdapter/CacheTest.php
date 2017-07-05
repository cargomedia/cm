<?php

class CM_Model_StorageAdapter_CacheTest extends CMTest_TestCase {

    public static function setupBeforeClass() {
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetCacheKey() {
        CM_Config::get()->CM_Model_Abstract = new stdClass();
        CM_Config::get()->CM_Model_Abstract->types = array(
            1 => 'CMTest_ModelMock_1',
            2 => 'CMTest_ModelMock_2',
        );

        $adapter = new CM_Model_StorageAdapter_Cache();
        $method = CMTest_TH::getProtectedMethod('CM_Model_StorageAdapter_Cache', '_getCacheKey');
        $this->assertSame('CM_Model_StorageAdapter_Cache_type:1_id:a:1:{s:2:"id";i:2;}', $method->invoke($adapter, 1, array('id' => 2)));
        $this->assertSame('CM_Model_StorageAdapter_Cache_type:2_id:a:1:{s:2:"id";i:3;}', $method->invoke($adapter, 2, array('id' => 3)));
    }

    public function testLoad() {
        CM_Config::get()->CM_Model_Abstract = new stdClass();
        CM_Config::get()->CM_Model_Abstract->types = array(
            1 => 'CMTest_ModelMock_1',
            2 => 'CMTest_ModelMock_2',
        );
        $id1 = 1;
        CM_Cache_Shared::getInstance()->set('CM_Model_StorageAdapter_Cache_type:1_id:a:1:{s:2:"id";i:1;}', array('foo' => 'foo1', 'bar' => $id1));
        $id2 = 2;
        CM_Cache_Shared::getInstance()->set('CM_Model_StorageAdapter_Cache_type:2_id:a:1:{s:2:"id";i:2;}', array('foo' => 'foo2', 'bar' => $id2));

        $adapter = new CM_Model_StorageAdapter_Cache();

        $this->assertSame(array('foo' => 'foo1', 'bar' => 1), $adapter->load($id1, array('id' => $id1)));
        $this->assertSame(array('foo' => 'foo2', 'bar' => 2), $adapter->load($id2, array('id' => $id2)));
        $this->assertFalse($adapter->load(1, array('id' => '9999')));
        $this->assertFalse($adapter->load(1, array('id' => $id1, 'foo' => 'foo1')));
    }

    public function testSave() {
        $id1 = 1;
        $cacheKey1 = 'CM_Model_StorageAdapter_Cache_type:1_id:a:1:{s:2:"id";i:1;}';
        CM_Cache_Shared::getInstance()->set($cacheKey1, array('foo' => 'foo1', 'bar' => $id1));
        $id2 = 2;
        $cacheKey2 = 'CM_Model_StorageAdapter_Cache_type:1_id:a:1:{s:2:"id";i:2;}';
        CM_Cache_Shared::getInstance()->set($cacheKey2, array('foo' => 'foo2', 'bar' => $id2));
        $type = 1;

        $adapter = new CM_Model_StorageAdapter_Cache();
        $adapter->save($type, array('id' => $id1), array('foo' => 'hello', 'bar' => 55));
        $this->assertSame(array('foo' => 'hello', 'bar' => 55), CM_Cache_Shared::getInstance()->get($cacheKey1));
        $this->assertSame(array('foo' => 'foo2', 'bar' => 2), CM_Cache_Shared::getInstance()->get($cacheKey2));
    }

    /**
     * @expectedException CM_Exception_NotImplemented
     */
    public function testCreate() {
        $adapter = new CM_Model_StorageAdapter_Cache();
        $adapter->create(1, array('foo' => 'foo1', 'bar' => 23));
    }

    public function testDelete() {
        $adapter = new CM_Model_StorageAdapter_Cache();
        $type = 1;
        $id = array('id' => 1);

        $adapter->save($type, $id, array('foo' => 'foo1', 'bar' => 23));
        $this->assertSame(array('foo' => 'foo1', 'bar' => 23), $adapter->load($type, $id));

        $adapter->delete($type, $id);
        $this->assertFalse($adapter->load($type, $id));
    }
}
