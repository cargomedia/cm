<?php

class CM_PagingSource_Redis_ListTest extends CMTest_TestCase {

	/** @var CM_Redis_Client */
	private $_client;

	public function setUp() {
		$this->_client = CM_Redis_Client::getInstance();
	}

	public function tearDown() {
		$this->_client->flush();
	}

	public function testGet() {
		$source = new CM_PagingSource_Redis_List('foo');
		$this->assertSame(0, $source->getCount());
		$this->assertSame(array(), $source->getItems());

		$this->_client->rPush('foo', 'bar1');
		$this->_client->rPush('foo', 'bar2');
		$this->_client->rPush('foo', 'bar3');
		$this->assertSame(3, $source->getCount());
		$this->assertSame(array('bar1', 'bar2', 'bar3'), $source->getItems());
		$this->assertSame(array('bar2'), $source->getItems(1, 1));
	}
}
