<?php

class CM_Cache_Storage_AbstractTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Cache_Storage_Runtime::getInstance()->flush();
	}

	public function testGetSet() {
		$cacheStorage = $this->getMockBuilder('CM_Cache_Storage_Abstract')
			->setMethods(array('_set', '_get', '_getKeyArmored'))->getMockForAbstractClass();
		$cacheStorage->expects($this->any())->method('_getKeyArmored')->will($this->returnCallback(function ($key) {
			return 'armor-' . $key;
		}));
		$cacheStorage->expects($this->exactly(2))->method('_get')->with('armor-foo')->will($this->onConsecutiveCalls(false, 'cached-bar'));
		$cacheStorage->expects($this->once())->method('_set')->with('armor-foo', 'bar', 100);

		$cacheRuntime = new CM_Cache_Storage_Runtime();

		$cache = $this->getMockBuilder('CM_Cache_Abstract')->setMethods(array('_getStorage', '_getRuntime'))
			->disableOriginalConstructor()->getMockForAbstractClass();
		$cache->expects($this->any())->method('_getStorage')->will($this->returnValue($cacheStorage));
		$cache->expects($this->any())->method('_getRuntime')->will($this->returnValue($cacheRuntime));
		/** @var CM_Cache_Abstract $cache */
		$this->assertFalse($cache->get('foo'));
		$cache->set('foo', 'bar', 100);
		$this->assertSame('bar', $cache->get('foo'));
		$cacheRuntime->flush();
		$this->assertSame('cached-bar', $cache->get('foo'));
	}

	public function testRuntime() {
		$cacheStorage = $this->getMockBuilder('CM_Cache_Storage_Abstract')->setMethods(array('_set', '_get'))->getMockForAbstractClass();
		$cacheStorage->expects($this->any())->method('_get')->will($this->returnValue('bar'));

		$cacheRuntime = new CM_Cache_Storage_Runtime();

		$cache = $this->getMockBuilder('CM_Cache_Abstract')->setMethods(array('_getStorage', '_getRuntime'))
			->disableOriginalConstructor()->getMockForAbstractClass();
		$cache->expects($this->any())->method('_getStorage')->will($this->returnValue($cacheStorage));
		$cache->expects($this->any())->method('_getRuntime')->will($this->returnValue($cacheRuntime));
		/** @var CM_Cache_Abstract $cache */

		$this->assertFalse($cacheRuntime->get('foo'));
		$cache->get('foo');
		$this->assertSame('bar', $cacheRuntime->get('foo'));
		$cache->set('foo', 'zoo', 100);
		$this->assertSame('zoo', $cacheRuntime->get('foo'));
	}
}
