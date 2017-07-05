<?php

class CM_Cache_Storage_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetSet() {
        $cacheStorage = $this->getMockBuilder('CM_Cache_Storage_Abstract')->setMethods(array('_set', '_get',
            '_getRuntime'))->getMockForAbstractClass();
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
}
