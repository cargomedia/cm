<?php

class CM_PagingSourceTest extends CMTest_TestCase {

	public function setUp() {
		CM_Db_Db::exec('CREATE TABLE `test` (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 0; $i < 100; $i++) {
			CM_Db_Db::insert('test', array('num' => $i));
		}
	}

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testCacheLocal() {
		$source = new CM_PagingSource_Sql('`num`', 'test');
		$source->enableCacheLocal();
		$this->assertEquals(100, $source->getCount());

		CM_Db_Db::delete('test', array('num' => 0));
		$this->assertEquals(100, $source->getCount());
		$source->clearCache();
		$this->assertEquals(100, $source->getCount());
	}

	public function testCache() {
		$source = new CM_PagingSource_Sql('`num`', 'test');
		$source->enableCache();
		$sourceNocache = new CM_PagingSource_Sql('`id`, num`', 'test');
		$this->assertEquals(100, $source->getCount());
		$this->assertEquals(100, $sourceNocache->getCount());

		CM_Db_Db::delete('test', array('num' => 0));
		$this->assertEquals(100, $source->getCount());
		$this->assertEquals(99, $sourceNocache->getCount());
		$source->clearCache();
		$this->assertEquals(99, $source->getCount());
		$this->assertEquals(99, $sourceNocache->getCount());
	}
}
