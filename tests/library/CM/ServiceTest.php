<?php

class DummyService {

    function getFoo() {
        return 'foo';
    }
}

class CM_ServicesTest extends CMTest_TestCase {

    public function setUp() {
        CM_Services::getInstance()->registerService('DummyService', array('class' => 'DummyService'));
    }

    public function assertRegisterService() {
        $this->assertNotEmpty('foo', CM_Services::getInstance()->getDummyService());
    }

    public function testServiceMethod() {
        $this->assertSame('foo', CM_Services::getInstance()->getDummyService()->getFoo());
    }

    public function testInstanceCaching() {
        $instance1 = CM_Services::getInstance()->getDummyService();
        $instance2 = CM_Services::getInstance()->getDummyService();

        $this->assertSame($instance1, $instance2);
    }

    public function testInvalidService() {
        try {
            CM_Services::getInstance()->getNonExistingService();
            $this->fail('Non existing service should fail.');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testMagicGet() {
        $instance1 = CM_Services::getInstance()->getDummyService();
        $instance2 = CM_Services::getInstance()->getServiceInstance('DummyService');

        $this->assertSame($instance1, $instance2);
    }
}
