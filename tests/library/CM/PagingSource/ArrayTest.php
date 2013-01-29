<?php

class CM_PagingSource_ArrayTest extends CMTest_TestCase {

	public function testGetCount() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10));
		$this->assertSame(10, $pagingSource->getCount());
		$this->assertSame(10, $pagingSource->getCount(5));

		$pagingSource = new CM_PagingSource_Array(array());
		$this->assertSame(0, $pagingSource->getCount());

	}

	public function testGetItems() {
		$pagingSource = new CM_PagingSource_Array(range(1, 10));
		$this->assertSame(range(1,10), $pagingSource->getItems());
		$this->assertSame(range(6,10), $pagingSource->getItems(5));
		$this->assertSame(range(1,5), $pagingSource->getItems(null, 5));
		$this->assertSame(range(4,8), $pagingSource->getItems(3,5));

		$pagingSource = new CM_PagingSource_Array(array());
		$this->assertSame(array(), $pagingSource->getItems());
		$this->assertSame(array(), $pagingSource->getItems(2));
	}

}
