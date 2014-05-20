<?php

class CM_Service_ManagerTest extends CMTest_TestCase {

    public function testHas() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('foo', 'DummyService', array('bar'));
        $serviceManager->registerInstance('bar', 'my-service');

        $this->assertTrue($serviceManager->has('foo'));
        $this->assertTrue($serviceManager->has('bar'));
        $this->assertFalse($serviceManager->has('foobar'));
    }

    public function testGet() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testGetAssertInstanceOf() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService', 'DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testGetWithMethod() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'), 'getArray', array('foo', 1234));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertSame(array('foo' => 1234), $serviceManager->get('DummyService'));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Service `DummyService` is a `DummyService`, but not `SomethingElse`.
     */
    public function testGetAssertInstanceOfInvalid() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        $serviceManager->get('DummyService', 'SomethingElse');
    }

    public function testServiceMethod() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertSame('bar', $service->getFoo());
    }

    public function testInstanceCaching() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));

        $service1 = $serviceManager->get('DummyService');
        $service2 = $serviceManager->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Service InvalidService has no config.
     */
    public function testInvalidService() {
        $serviceManager = new CM_Service_Manager();

        $serviceManager->get('InvalidService');
    }

    public function testMagicGet() {
        $serviceManager = new CM_Service_Manager();
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
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('bar'));
        $serviceManager->register('DummyService', 'DummyService', array('bar'));
    }

    public function testRegisterInstance() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('foo', 12.3);
        $this->assertSame(12.3, $serviceManager->get('foo'));
    }

    public function testUnregister() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('foo', 12.3);
        $this->assertSame(true, $serviceManager->has('foo'));

        $serviceManager->unregister('foo');
        $this->assertSame(false, $serviceManager->has('foo'));
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

    public function getArray($key, $value) {
        return array($key => $value);
    }
}
