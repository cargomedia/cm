<?php

class CM_ServicesTest extends CMTest_TestCase {

    public function setUp() {
        CM_ServiceManager::getInstance()->register('DummyService', 'DummyService');
    }

    public function assertRegisterService() {
        $this->assertNotEmpty('foo', CM_ServiceManager::getInstance()->getDummyService());
    }

    public function testServiceMethod() {
        $this->assertSame('foo', CM_ServiceManager::getInstance()->getDummyService()->getFoo());
    }

    public function testInstanceCaching() {
        $instance1 = CM_ServiceManager::getInstance()->getDummyService();
        $instance2 = CM_ServiceManager::getInstance()->getDummyService();

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @expectedException CM_Exception_Nonexistent
     * @expectedExceptionMessage Service NonExistingService is not registered.
     */
    public function testInvalidService() {
        CM_ServiceManager::getInstance()->getNonExistingService();
    }

    public function testMagicGet() {
        $instance1 = CM_ServiceManager::getInstance()->getDummyService();
        $instance2 = CM_ServiceManager::getInstance()->get('DummyService');

        $this->assertSame($instance1, $instance2);
    }
}

class DummyService {

    function getFoo() {
        return 'foo';
    }
}
