<?php

class CM_PagingSource_ArrayTest extends CMTest_TestCase {

	public function testClearCache() {
		$pagingMock = $this->getMock('CM_PagingSource_Sql', array('clearCache'), array('*', 'table'));
		$pagingMock->expects($this->once())->method('clearCache');
		$pagingSource = new CM_PagingSource_Array($pagingMock);
		$pagingSource->clearCache();
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage does not support caching
	 */
	public function testClearCacheNotImplemented() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10));
		$pagingSource->clearCache();
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage does not support caching
	 */
	public function testClearCacheNotImplementedOnSource() {
		$pagingSource = new CM_PagingSource_Array(new CM_PagingSource_Array(range(1, 10)));
		$pagingSource->clearCache();
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Paging data should be either an array or a paging source.
	 */
	public function testDataInvalid() {
		new CM_PagingSource_Array('abc');
	}

	public function testFilter() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10), function ($item) {
			return $item % 2 === 0;
		});
		$this->assertSame(array(2, 4, 6, 8, 10), $pagingSource->getItems());
	}

	public function testFilterSort() {
		$items = range(1, 10);
		shuffle($items);

		$pagingSource = new CM_PagingSource_Array($items, function ($item) {
			return $item % 3 === 1;
		}, function ($item) {
			return $item;
		}, SORT_DESC, SORT_STRING);
		$this->assertSame(array(7, 4, 10, 1), $pagingSource->getItems());
	}

	public function testGetCount() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10));
		$this->assertSame(10, $pagingSource->getCount());
		$this->assertSame(10, $pagingSource->getCount(5));

		$pagingSource = new CM_PagingSource_Array(array());
		$this->assertSame(0, $pagingSource->getCount());
	}

	public function testGetItems() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10));
		$this->assertSame(range(1, 10), $pagingSource->getItems());
		$this->assertSame(range(6, 10), $pagingSource->getItems(5));
		$this->assertSame(range(1, 5), $pagingSource->getItems(null, 5));
		$this->assertSame(range(4, 8), $pagingSource->getItems(3, 5));

		$pagingSource = new CM_PagingSource_Array(array());
		$this->assertSame(array(), $pagingSource->getItems());
		$this->assertSame(array(), $pagingSource->getItems(2));
	}

	public function testSort() {
		$items = range(1, 10);
		shuffle($items);

		$pagingSource = new CM_PagingSource_Array($items, null, function ($item) {
			return $item;
		});
		$this->assertSame(range(1, 10), $pagingSource->getItems());

		$pagingSource = new CM_PagingSource_Array($items, null, function ($item) {
			return $item;
		}, SORT_DESC);
		$this->assertSame(range(10, 1), $pagingSource->getItems());

		$pagingSource = new CM_PagingSource_Array($items, null, function ($item) {
			return $item;
		}, null, SORT_STRING);
		$this->assertSame(array(1, 10, 2, 3, 4, 5, 6, 7, 8, 9), $pagingSource->getItems());

		$pagingSource = new CM_PagingSource_Array($items, null, function ($item) {
			return $item;
		}, SORT_DESC, SORT_STRING);
		$this->assertSame(array(9, 8, 7, 6, 5, 4, 3, 2, 10, 1), $pagingSource->getItems());
	}
}
