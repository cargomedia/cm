<?php

class CM_Cache_AbstractTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Cache_Storage_Runtime::getInstance()->flush();
	}

	public function testGetSet() {
		$storageAdapter = $this->getMockBuilder('CM_Cache_Storage_Abstract')
				->setMethods(array('_set', '_get', '_getKeyArmored'))->getMockForAbstractClass();
		$storageAdapter->expects($this->any())->method('_getKeyArmored')->will($this->returnCallback(function ($key) {
			return 'armor-' . $key;
		}));
		$storageAdapter->expects($this->exactly(2))->method('_get')->with('armor-foo')->will($this->onConsecutiveCalls(false, 'cached-bar'));
		$storageAdapter->expects($this->once())->method('_set')->with('armor-foo', 'bar', 100);

		$cache = $this->getMockBuilder('CM_Cache_Abstract')->setMethods(array('_getStorage'))->disableOriginalConstructor()->getMockForAbstractClass();
		$cache->expects($this->any())->method('_getStorage')->will($this->returnValue($storageAdapter));
		/** @var CM_Cache_Abstract $cache */
		$this->assertFalse($cache->get('foo'));
		$cache->set('foo', 'bar', 100);
		$this->assertSame('bar', $cache->get('foo'));
		CM_Cache_Storage_Runtime::getInstance()->flush();
		$this->assertSame('cached-bar', $cache->get('foo'));
	}

	public function testRuntime() {
		$storageAdapter = $this->getMockBuilder('CM_Cache_Storage_Abstract')->setMethods(array('_set', '_get'))->getMockForAbstractClass();
		$storageAdapter->expects($this->any())->method('_get')->will($this->returnValue('bar'));

		$cache = $this->getMockBuilder('CM_Cache_Abstract')->setMethods(array('_getStorage'))->disableOriginalConstructor()->getMockForAbstractClass();
		$cache->expects($this->any())->method('_getStorage')->will($this->returnValue($storageAdapter));
		/** @var CM_Cache_Abstract $cache */

		$runtimeCache = CM_Cache_Storage_Runtime::getInstance();
		$this->assertFalse($runtimeCache->get('foo'));
		$cache->get('foo');
		$this->assertSame('bar', $runtimeCache->get('foo'));
		$cache->set('foo', 'zoo', 100);
		$this->assertSame('zoo', $runtimeCache->get('foo'));
	}
}
