<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_PagingSourceTest extends TestCase {
	public static function setUpBeforeClass() {
	}
	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function setUp() {
		define('TBL_TEST', 'test');
		CM_Mysql::exec('CREATE TABLE TBL_TEST (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 0; $i < 100; $i++) {
			CM_Mysql::insert(TBL_TEST, array('num' => $i));
		}
	}
	public function tearDown() {
		CM_Mysql::exec('DROP TABLE TBL_TEST');
	}

	public function testCacheLocal() {
		$source = new CM_PagingSource_Sql('`num`', TBL_TEST);
		$source->enableCacheLocal();
		$this->assertEquals(100, $source->getCount());

		CM_Mysql::delete(TBL_TEST, array(num => 0));
		$this->assertEquals(100, $source->getCount());
		$source->clearCache();
		$this->assertEquals(100, $source->getCount());
	}

	public function testCache() {
		$source = new CM_PagingSource_Sql('`num`', TBL_TEST);
		$source->enableCache();
		$sourceNocache = new CM_PagingSource_Sql('`id`, num`', TBL_TEST);
		$this->assertEquals(100, $source->getCount());
		$this->assertEquals(100, $sourceNocache->getCount());

		CM_Mysql::delete(TBL_TEST, array(num => 0));
		$this->assertEquals(100, $source->getCount());
		$this->assertEquals(99, $sourceNocache->getCount());
		$source->clearCache();
		$this->assertEquals(99, $source->getCount());
		$this->assertEquals(99, $sourceNocache->getCount());
	}
}
