<?php

class CM_ServiceManagerTest extends CMTest_TestCase {

    public function setUp() {
        CM_ServiceManager::getInstance()->register('DummyService', 'DummyService', array('bar'));
    }

    public function testHas() {
        $this->assertTrue(CM_ServiceManager::getInstance()->has('DummyService'));
    }

    public function testGet() {
        /** @var DummyService $service */
        $service = CM_ServiceManager::getInstance()->get('DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testServiceMethod() {
        /** @var DummyService $service */
        $service = CM_ServiceManager::getInstance()->get('DummyService');
        $this->assertSame('bar', $service->getFoo());
    }

    public function testInstanceCaching() {
        $service1 = CM_ServiceManager::getInstance()->get('DummyService');
        $service2 = CM_ServiceManager::getInstance()->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    /**
     * @expectedException CM_Exception_Nonexistent
     * @expectedExceptionMessage Service `InvalidService` is not registered.
     */
    public function testInvalidService() {
        CM_ServiceManager::getInstance()->get('InvalidService');
    }

    public function testMagicGet() {
        $service1 = CM_ServiceManager::getInstance()->getDummyService();
        $service2 = CM_ServiceManager::getInstance()->get('DummyService');
        $this->assertSame($service1, $service2);
    }
}

class DummyService {

    private $_foo;

    public function __construct($foo) {
        $this->_foo = $foo;
    }

    public function getFoo() {
        return $this->_foo;
    }
}
