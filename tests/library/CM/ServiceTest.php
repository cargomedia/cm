<?php

class CM_ServicesTest extends CMTest_TestCase {

    public function testInstanceCaching() {
        $instance1 = CM_Services::getInstance()->getMongoDB();
        $instance2 = CM_Services::getInstance()->getMongoDB();

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
        $instance1 = CM_Services::getInstance()->getMongoDB();
        $instance2 = CM_Services::getInstance()->getServiceInstance('MongoDB');

        $this->assertSame($instance1, $instance2);
    }
}
