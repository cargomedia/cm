<?php

class CM_ServiceManagerTest extends CMTest_TestCase {

    public function testHas() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        $this->assertTrue($serviceManager->has('DummyService'));
    }

    public function testGet() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testServiceMethod() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertSame('bar', $service->getFoo());
    }

    public function testInstanceCaching() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        $service1 = $serviceManager->get('DummyService');
        $service2 = $serviceManager->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    /**
     * @expectedException CM_Exception_Nonexistent
     * @expectedExceptionMessage Service InvalidService is not registered.
     */
    public function testInvalidService() {
        $serviceManager = new CM_ServiceManager();

        $serviceManager->get('InvalidService');
    }

    public function testMagicGet() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        $service1 = $serviceManager->getDummyService();
        $service2 = $serviceManager->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Service `DummyService` already registered
     */
    public function testRegisterTwice() {
        $serviceManager = new CM_ServiceManager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));
        $serviceManager->register('DummyService', 'DummyService', array('bar'));
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
