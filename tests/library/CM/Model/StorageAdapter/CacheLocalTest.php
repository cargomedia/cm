<?php

class CM_Model_StorageAdapter_CacheLocalTest extends CMTest_TestCase {

    public static function setupBeforeClass() {
        if (!ini_get('apc.enable_cli')) {
            self::markTestSkipped('APC must be enabled for the cli for this test to work');
        }
    }

    public function tearDown() {
        CM_Cache_Local::getInstance()->flush();
    }

    public function testSaveLoadDelete() {
        $adapter = new CM_Model_StorageAdapter_CacheLocal();
        $adapter->save(12, ['id' => 13], ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $adapter->load(12, ['id' => 13]));

        $adapter->delete(12, ['id' => 13]);
        $this->assertSame(false, $adapter->load(12, ['id' => 13]));
    }
}
