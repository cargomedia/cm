<?php

class CM_Cache_AbstractTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Cache_Storage_Runtime::getInstance()->flush();
	}

	public function testGetSet() {
		$storageAdapter = $this->getMockBuilder('CM_Cache_StorageAdapter_Abstract')->setMethods(array('set', 'get'))->getMockForAbstractClass();
		$storageAdapter->expects($this->exactly(2))->method('_get')->with('foo')->will($this->onConsecutiveCalls(false, 'cached-bar'));
		$storageAdapter->expects($this->once())->method('_set')->with('foo', 'bar', 100);

		$cache = $this->getMockClass('CM_Cache_Abstract', array('getStorage'));
		/** @var PHPUnit_Framework_MockObject_MockObject $cache */
		$cache::staticExpects($this->any())->method('getStorage')->will($this->returnValue($storageAdapter));
		/** @var CM_Cache_Abstract $cache */
		$this->assertFalse($cache::get('foo'));
		$cache::set('foo', 'bar', 100);
		$this->assertSame('bar', $cache::get('foo'));
		CM_Cache_Storage_Runtime::getInstance()->flush();
		$this->assertSame('cached-bar', $cache::get('foo'));
	}
}
