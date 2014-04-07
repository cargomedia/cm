<?php

class CM_Service_MongoDBTest extends CMTest_TestCase {

    public function testInstanceCaching() {
        $instance1 = CM_Services::getInstance()->getMongoDB();
        $instance2 = CM_Services::getInstance()->getMongoDB();

        $this->assertSame($instance1, $instance2);
    }
}
