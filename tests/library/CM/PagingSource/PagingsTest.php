<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_PagingSource_PagingsTest extends TestCase {

	public static function setUpBeforeClass() {
		define('TBL_TEST_A', 'test_a');
		define('TBL_TEST_B', 'test_b');
		CM_Mysql::exec('CREATE TABLE TBL_TEST_A (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 0; $i < 10; $i++) {
			CM_Mysql::insert(TBL_TEST_A, array('num' => $i % 5));
		}
		CM_Mysql::exec('CREATE TABLE TBL_TEST_B (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`num` INT(10) NOT NULL,
						PRIMARY KEY (`id`)
						)');
		for ($i = 0; $i < 5; $i++) {
			CM_Mysql::insert(TBL_TEST_B, array('num' => 5 - $i));
		}
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetCount() {
		$pagingA = new CM_Paging_A();
		$this->assertEquals(10, $pagingA->getCount());
		$pagingB = new CM_Paging_B();
		$this->assertEquals(5, $pagingB->getCount());
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'id');
		$this->assertEquals(15, $pagingSource->getCount());

		$this->assertEquals(4, $pagingSource->getCount(11));

		//duplicate elimination
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'id', true);
		$this->assertEquals(10, $pagingSource->getCount());

		//invalid field
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'nonexistent');
		$this->assertEquals(0, $pagingSource->getCount());
	}

	public function testGetItems() {
		$pagingA = new CM_Paging_A();
		$this->assertEquals(10, $pagingA->getCount());
		$pagingB = new CM_Paging_B();
		$this->assertEquals(5, $pagingB->getCount());
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'id');
		$items = $pagingSource->getItems(8, 5);
		$this->assertEquals(9, reset($items));
		$this->assertEquals(3, end($items));

		//duplicate elimination
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'id', true);
		$items = $pagingSource->getItems(8, 5);
		$this->assertEquals(9, reset($items));
		$this->assertEquals(10, end($items));
		$this->assertEquals(2, count($items));

		//invalid field
		$pagingSource = new CM_PagingSource_Pagings(array($pagingA, $pagingB), 'nonexistent');
		$this->assertEmpty($pagingSource->getItems());
	}
}

class CM_Paging_A extends CM_Paging_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`id`, `num`', TBL_TEST_A);
		parent::__construct($source);
	}
}

class CM_Paging_B extends CM_Paging_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`id`, `num`', TBL_TEST_B);
		parent::__construct($source);
	}
}
