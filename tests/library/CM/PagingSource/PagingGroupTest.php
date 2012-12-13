<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_PagingSource_PagingGroupTest extends TestCase {

	public function testGetCount() {
		$pagingSource = $this->_getPagingSource();

		$this->assertSame(20, $pagingSource->getCount());
		$this->assertSame(20, $pagingSource->getCount(5));
	}

	public function testGetItems() {
		$pagingSource = $this->_getPagingSource();

		$itemList = $pagingSource->getItems();
		$this->assertSame(20, count($itemList));
		$this->assertSame(array(1,21,41,61,81), $itemList[1]);

		$itemList = $pagingSource->getItems(5);
		$this->assertSame(15, count($itemList));
		$this->assertSame(array(5,25,45,65,85), $itemList[0]);
	}

	private function _getPagingSource() {
		$paging = $this->getMockForAbstractClass('CM_Paging_Abstract', array(new CM_PagingSource_Array(range(0, 100))));
		$pagingSource = new CM_PagingSource_PagingGroup($paging, function($value) {
			return $value % 20 . 'keyValue';
		});

		return $pagingSource;
	}
}


