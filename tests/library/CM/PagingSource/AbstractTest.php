<?php

class CM_PagingSource_AbstractTest extends CMTest_TestCase {

    public function setUp() {
        CM_Db_Db::exec('CREATE TABLE `test` (
						`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
        for ($i = 0; $i < 100; $i++) {
            CM_Db_Db::insert('test', array('num' => $i));
        }
    }

    public function tearDown() {
        CM_Db_Db::exec('DROP TABLE `test`');
        $paging = new CM_PagingSource_Sql('`num`', 'test');
        $paging->enableCache();
        $paging->clearCache();
    }

    public function testCacheLocal() {
        $source = new CM_PagingSource_Sql('`num`', 'test');
        $source->enableCacheLocal();
        $this->assertEquals(100, $source->getCount());

        CM_Db_Db::delete('test', array('num' => 0));
        $this->assertEquals(100, $source->getCount());
        $source->clearCache();
        $this->assertEquals(99, $source->getCount());
        CM_Db_Db::delete('test', array('num' => 1));
        $this->assertEquals(99, $source->getCount());
        CM_Cache_Local::getInstance()->flush();
        $this->assertEquals(98, $source->getCount());
    }

    public function testCacheShared() {
        $source = new CM_PagingSource_Sql('`num`', 'test');
        $source->enableCache();
        $this->assertSame(100, $source->getCount());

        $sourceNocache = new CM_PagingSource_Sql('`num`', 'test');
        $this->assertSame(100, $sourceNocache->getCount());

        CM_Db_Db::delete('test', array('num' => 0));
        $this->assertSame(100, $source->getCount());
        $this->assertSame(99, $sourceNocache->getCount());
        $source->clearCache();
        $this->assertSame(99, $source->getCount());
        $this->assertSame(99, $sourceNocache->getCount());
        CM_Db_Db::delete('test', array('num' => 1));
        $this->assertSame(99, $source->getCount());
        $this->assertSame(98, $sourceNocache->getCount());
        CM_Cache_Shared::getInstance()->flush();
        $this->assertSame(98, $source->getCount());
        CM_Cache_Shared::getInstance()->flush();
    }

    public function testCacheCustom() {
        $source = new CM_PagingSource_Sql('`num`', 'test');
        $fileCache = CM_Cache_Persistent::getInstance();
        $source->enableCache(null, $fileCache);
        $this->assertEquals(100, $source->getCount());

        CM_Db_Db::delete('test', array('num' => 0));
        $this->assertEquals(100, $source->getCount());
        $source->clearCache();
        $this->assertEquals(99, $source->getCount());
        CM_Db_Db::delete('test', array('num' => 1));
        $this->assertEquals(99, $source->getCount());
        $fileCache->flush();
        $this->assertEquals(98, $source->getCount());
    }
}
