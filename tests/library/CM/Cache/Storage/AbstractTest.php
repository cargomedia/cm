<?php

class CM_Cache_Storage_AbstractTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Cache_Storage_Runtime::getInstance()->flush();
	}

	public function testGetSet() {
		$cacheStorage = $this->getMockBuilder('CM_Cache_Storage_Abstract')->setMethods(array('_set', '_get', '_getRuntime'))->getMockForAbstractClass();
		$localStorage = array();
		$cacheStorage->expects($this->any())->method('_set')->will(new PHPUnit_Framework_MockObject_Stub_ReturnCallback(function ($key, $value) use (&$localStorage) {
			$localStorage[$key] = $value;
		}));
		$cacheStorage->expects($this->exactly(2))->method('_get')->will(new PHPUnit_Framework_MockObject_Stub_ReturnCallback(function ($key) use (&$localStorage) {
			if (!array_key_exists($key, $localStorage)) {
				return false;
			}
			return $localStorage[$key];
		}));
		$cacheRuntime = new CM_Cache_Storage_Runtime();
		$cacheStorage->expects($this->any())->method('_getRuntime')->will($this->returnValue($cacheRuntime));

		/** @var CM_Cache_Abstract $cacheStorage */
		$this->assertFalse($cacheStorage->get('foo'));
		$cacheStorage->set('foo', 'bar');
		$this->assertSame('bar', $cacheStorage->get('foo'));
		$cacheRuntime->flush();
		$this->assertSame('bar', $cacheStorage->get('foo'));
	}

	public function testGetMulti() {
		$cacheRuntime = new CM_Cache_Storage_Runtime();
		$cacheStorage = $this->getMockBuilder('CM_Cache_Storage_Memcache')->setMethods(array('_getRuntime'))->getMock();
		$cacheStorage->expects($this->any())->method('_getRuntime')->will($this->returnValue($cacheRuntime));

		$this->assertSame(false, $cacheRuntime->get('foo'));
		$this->assertSame(false, $cacheRuntime->get('missed'));
		$cacheStorage->set('foo', 'bar');
		$this->assertSame(array('foo' => 'bar'), $cacheStorage->getMulti(array('foo', 'missed')));
		$this->assertSame('bar', $cacheRuntime->get('foo'));
		$this->assertSame(false, $cacheRuntime->get('missed'));
	}
}
