<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_PagingSource_PagingGroupTest extends TestCase {

	/** @var CM_PagingSource_PagingGroup */
	private static $_pagingSource;

	public static function setUpBeforeClass() {
		$pagingSourceValues = array();
		for ($i = 0; $i < 100; $i++) {
			$pagingSourceValues[] = $i;
		}

		$paging = new CM_Paging_MockPagingGroup(new CM_PagingSource_Array($pagingSourceValues));
		self::$_pagingSource = new CM_PagingSource_PagingGroup($paging, function($value) {
			return $value % 10;
		});
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testGetCount() {
		$this->assertSame(10, self::$_pagingSource->getCount());
		$this->assertSame(10, self::$_pagingSource->getCount(5));
	}

	public function testGetItems() {
		$itemList = self::$_pagingSource->getItems();
		$this->assertSame(10, count($itemList));
		$this->assertSame(1, $itemList[1][0]);

		$itemList = self::$_pagingSource->getItems(5);
		$this->assertSame(5, count($itemList));
		$this->assertSame(25, $itemList[0][2]);
	}
}

class CM_Paging_MockPagingGroup extends CM_Paging_Abstract {
	protected function _processItem($itemRaw) {
		return (int) $itemRaw;
	}
}
