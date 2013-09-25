<?php

class CM_PagingSource_PagingsTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Db_Db::exec('CREATE TABLE `test_a` (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 1; $i <= 10; $i++) {
			CM_Db_Db::insert('test_a', array('num' => $i % 5));
		}
		CM_Db_Db::exec('CREATE TABLE `test_b` (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 1; $i <= 5; $i++) {
			CM_Db_Db::insert('test_b', array('num' => $i % 5));
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		CM_Db_Db::exec('DROP TABLE `test_a`');
		CM_Db_Db::exec('DROP TABLE `test_b`');
	}

	public function testCaching() {
		$source = new CM_PagingSource_Pagings(array(new CM_Paging_A()));
		$source->enableCache();
		try {
			$source->clearCache();
			$this->fail('CM_PagingSource_Pagings should not use caching.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testGetCount() {
		$pagingA = new CM_Paging_A();
		$this->assertEquals(10, $pagingA->getCount());
		$pagingB = new CM_Paging_B();
		$this->assertEquals(5, $pagingB->getCount());
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB));
		$this->assertEquals(15, $pagingSource->getCount());

		$this->assertEquals(15, $pagingSource->getCount(11));

		//duplicate elimination
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), true);
		$this->assertEquals(10, $pagingSource->getCount());
	}

	public function testGetItems() {
		$pagingA = new CM_Paging_A();
		$this->assertEquals(10, $pagingA->getCount());
		$pagingB = new CM_Paging_B();
		$this->assertEquals(5, $pagingB->getCount());
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB));
		$items = $pagingSource->getItems(8, 5);
		$this->assertEquals(array('id' => 9, 'num' => 4), reset($items));
		$this->assertEquals(array('id' => 3, 'num' => 3), end($items));

		//duplicate elimination
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), true);
		$items = $pagingSource->getItems(8, 5);
		$this->assertEquals(array('id' => 9, 'num' => 4), reset($items));
		$this->assertEquals(array('id' => 10, 'num' => 0), end($items));
		$this->assertEquals(2, count($items));

		$items = $pagingSource->getItems(null, 3);
		$this->assertEquals(array('id' => 1, 'num' => 1), reset($items));
		$this->assertEquals(array('id' => 3, 'num' => 3), end($items));
		$this->assertEquals(3, count($items));
	}
}

class CM_Paging_A extends CM_Paging_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`id`, `num`', 'test_a');
		parent::__construct($source);
	}
}

class CM_Paging_B extends CM_Paging_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`id`, `num`', 'test_b');
		parent::__construct($source);
	}
}
