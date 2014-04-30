<?php

class CM_Cache_Storage_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CM_Cache_Storage_Runtime::getInstance()->flush();
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
