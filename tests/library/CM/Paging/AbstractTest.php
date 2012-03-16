<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Paging_Mock extends CM_Paging_Abstract {
	protected function _processItem($itemRaw) {
		return (int) $itemRaw;
	}
}

class CM_Paging_Mock_Gaps extends CM_Paging_Mock {
	protected function _processItem($itemRaw) {
		if ($itemRaw % 3 == 0) {
			throw new CM_Exception_Nonexistent();
		}
		return parent::_processItem($itemRaw);
	}
}

class CM_Comparable_Mock implements CM_Comparable {
	private $_value;

	public function __construct($value) {
		$this->_value = $value;
	}

	public function getValue() {
		return $this->_value;
	}

	public function equals(self $other = null) {
		return ($other && $this->getValue() == $other->getValue());
	}
}

class CM_PagingSource_Mock extends CM_PagingSource_Abstract {
	private $_items;

	public function __construct($min, $max) {
		$this->_items = range($min, $max);
	}

	public function getCount($offset = null, $count = null) {
		return count($this->_items);
	}

	public function getItems($offset = null, $count = null) {
		return $this->_items;
	}

	protected function _cacheKeyBase() {
		throw new CM_Exception_NotImplemented();
	}
}

class CM_PagingSource_MockStale extends CM_PagingSource_Mock {
	public function getStalenessChance() {
		return 0.5;
	}
}

class CM_Paging_Mock_Comparable extends CM_Paging_Mock {
	protected function _processItem($itemRaw) {
		return new CM_Comparable_Mock($itemRaw);
	}
}

class CM_Paging_AbstractTest extends TestCase {
	private static $_source, $_sourceStale;

	public static function setUpBeforeClass() {
		define('TBL_TEST', 'test');
		CM_Mysql::exec('CREATE TABLE TBL_TEST (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`num` INT(10) NOT NULL,
					PRIMARY KEY (`id`)
				)');
		for ($i = 0; $i < 100; $i++) {
			CM_Mysql::insert(TBL_TEST, array('num' => $i));
		}
		self::$_source = new CM_PagingSource_Sql('`num`', TBL_TEST);
		define('TBL_TEST2', 'test2');
		CM_Mysql::exec('CREATE TABLE TBL_TEST2 (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`num` INT(10) NOT NULL,
					PRIMARY KEY (`id`)
				)');
		for ($i = 0; $i < 50; $i++) {
			CM_Mysql::insert(TBL_TEST2, array('num' => $i));
			CM_Mysql::insert(TBL_TEST2, array('num' => $i));
		}
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
		CM_Mysql::exec('DROP TABLE TBL_TEST');
		CM_Mysql::exec('DROP TABLE TBL_TEST2');
	}

	public function testGetCount() {
		$paging = new CM_Paging_Mock(self::$_source);
		$this->assertEquals(100, $paging->getCount());
	}

	public function testGetCountGroup() {
		$paging = new CM_Paging_Mock(new CM_PagingSource_Sql('`num`', TBL_TEST2, null, null, null, '`num`'));
		$this->assertEquals(50, $paging->getCount());

		$paging = new CM_Paging_Mock(new CM_PagingSource_Sql('`num`', TBL_TEST2, 'id=1', null, null, '`num`'));
		$this->assertEquals(1, $paging->getCount());

		$paging = new CM_Paging_Mock(new CM_PagingSource_Sql('`num`', TBL_TEST2, 'id=99999', null, null, '`num`'));
		$this->assertEquals(0, $paging->getCount());
	}

	public function testSetGetPage() {
		$paging = new CM_Paging_Mock(self::$_source);
		$this->assertEquals(1, $paging->getPage());
		$this->assertEquals(0, $paging->getPageCount());

		$paging = new CM_Paging_Mock(self::$_source);
		$paging->setPage(2, 10);
		$this->assertEquals(2, $paging->getPage());
		$this->assertEquals(10, $paging->getPageCount());

		$paging->setPage(10, 10);
		$this->assertEquals(10, $paging->getPage());
		$this->assertEquals(10, $paging->getPageCount());

		$paging->setPage(11, 10);
		$this->assertEquals(10, $paging->getPage());
		$this->assertEquals(10, $paging->getPageCount());

		$paging->setPage(-1, 10);
		$this->assertEquals(1, $paging->getPage());
		$this->assertEquals(10, $paging->getPageCount());

		$paging->setPage(13, 9);
		$this->assertEquals(12, $paging->getPage());
		$this->assertEquals(12, $paging->getPageCount());

		$paging->setPage(1, 9999);
		$this->assertEquals(1, $paging->getPage());
		$this->assertEquals(1, $paging->getPageCount());
	}

	public function testSetPage() {
		$paging = new CM_Paging_Mock(self::$_source);
		$this->assertEquals(array(0, 1), $paging->setPage(1, 2)->getItems());
		$this->assertEquals(array(6, 7, 8), $paging->setPage(3, 3)->getItems());
	}

	public function testPageEmpty() {
		$paging = new CM_Paging_Mock();
		$this->assertSame(1, $paging->getPage());
		$this->assertSame(0, $paging->getPageCount());

		$paging = new CM_Paging_Mock();
		$paging->setPage(2, 10);
		$this->assertSame(1, $paging->getPage());
		$this->assertSame(0, $paging->getPageCount());

		$paging->setPage(1, 9999);
		$this->assertSame(1, $paging->getPage());
		$this->assertSame(0, $paging->getPageCount());
	}

	public function testIsEmpty() {
		$paging = new CM_Paging_Mock(self::$_source);
		$this->assertFalse($paging->isEmpty());

		$paging = new CM_Paging_Mock();
		$this->assertTrue($paging->isEmpty());
	}

	public function testGetItems() {
		$paging = new CM_Paging_Mock(self::$_source);
		$items = $paging->getItems();
		$this->assertInternalType('array', $items);
		$this->assertCount(100, $items);
		$this->assertSame(range(0, 99), $items);

		$itemsRaw = $paging->getItemsRaw();
		$this->assertInternalType('array', $itemsRaw);
		$this->assertCount(100, $itemsRaw);
		for ($i = 0; $i < 100; $i++) {
			$this->assertSame((string) $i, $itemsRaw[$i]);
		}

		$items = $paging->getItems();
		$this->assertSame(range(0, 99), $items);

		// Positive offset
		$items = $paging->getItems(5);
		$this->assertSame(range(5, 99), $items);

		$items = $paging->getItems(9999);
		$this->assertSame(array(), $items);

		// Negative offset
		$items = $paging->getItems(-5);
		$this->assertSame(range(95, 99), $items);

		$items = $paging->getItems(-9999);
		$this->assertSame(range(0, 99), $items);

		// Length
		$items = $paging->getItems(5, 3);
		$this->assertSame(range(5, 7), $items);

		$items = $paging->getItems(9999, 3);
		$this->assertSame(array(), $items);

		$items = $paging->getItems(-5, 3);
		$this->assertSame(range(95, 97), $items);

		$items = $paging->getItems(-9999, 3);
		$this->assertSame(range(0, 2), $items);

		$items = $paging->getItems(0, 9999);
		$this->assertSame(range(0, 99), $items);

		$items = $paging->getItems(0, 3);
		$this->assertSame(range(0, 2), $items);

		// Paged
		$paging->setPage(2, 10);
		$items = $paging->getItems();
		$this->assertInternalType('array', $items);
		$this->assertCount(10, $items);
		$this->assertSame(range(10, 19), $items);
	}

	public function testGetItem() {
		$paging = new CM_Paging_Mock(self::$_source);
		for ($i = 0; $i < 100; $i++) {
			$this->assertSame($i, $paging->getItem($i));
			$this->assertSame($i, $paging->getItem(-100 + $i));
		}

		$item = $paging->getItem(9999);
		$this->assertNull($item, 'Could getItem() of nonexistent index');
	}

	public function testStaleness() {
		$paging = new CM_Paging_Mock_Gaps(new CM_PagingSource_MockStale(0, 20));
		$paging->setPage(1, 10);
		$this->assertEquals(range(0, 9), $paging->getItemsRaw());
		$this->assertSame(array(1, 2, 4, 5, 7, 8, 10, 11, 13, 14), $paging->getItems());

		$paging = new CM_Paging_Mock_Gaps(new CM_PagingSource_MockStale(0, 20));
		$paging->setPage(1, 10);
		$this->assertEquals(range(0, 9), $paging->getItemsRaw());
		$this->assertSame(array(null, 1, 2, null, 4, 5, null, 7, 8, null), $paging->getItems(null, null, true));

		$paging = new CM_Paging_Mock_Gaps(new CM_PagingSource_Mock(0, 20));
		try {
			$paging->getItems();
			$this->fail('Getting stale data with a not-stale-expecting source did not throw exception');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}

	public function testFilter() {
		$paging = new CM_Paging_Mock(self::$_source);
		$paging->setPage(1, 10);
		$paging->filter(function ($item) {
			return ($item % 2 == 0);
		});
		$this->assertSame(array(0, 2, 4, 6, 8), $paging->getItems());

		$paging = new CM_Paging_Mock_Gaps(new CM_PagingSource_MockStale(0, 20));
		$paging->setPage(1, 10);
		$paging->filter(function ($item) {
			return ($item % 4 != 0);
		});
		$this->assertSame(array(1, 2, 5, 7, 10, 11, 13, 14, 17, 19), $paging->getItems());
	}

	public function testExclude() {
		$paging = new CM_Paging_Mock(self::$_source);
		$paging->setPage(1, 10);
		$paging->exclude(1);
		$paging->exclude(array(3, 5));
		$this->assertSame(array(0, 2, 4, 6, 7, 8, 9), $paging->getItems());
		$this->assertEquals(0, $paging->getItem(0));
		$this->assertEquals(2, $paging->getItem(1));

		$paging->exclude(2);
		$this->assertSame(array(0, 4, 6, 7, 8, 9), $paging->getItems());

		$paging = new CM_Paging_Mock_Comparable(new CM_PagingSource_Mock(1, 5));
		$paging->exclude(array(new CM_Comparable_Mock(3), new CM_Comparable_Mock(2)));
		$expected = array(new CM_Comparable_Mock(1), new CM_Comparable_Mock(4), new CM_Comparable_Mock(5));
		$this->assertEquals($expected, $paging->getItems());
	}
}
