<?php

class CM_Service_MongoDBTest extends CMTest_TestCase {

    public function testGetClient() {
        $mongo = CM_Services::getInstance()->getMongoDB();
        $this->assertInstanceOf('MongoClient', $mongo->getClient());
    }

    public function testGetDefaultDatabase() {
        $mongo = CM_Services::getInstance()->getMongoDB();
        $db = $mongo->getDatabase();
        $this->assertInstanceOf('MongoDB', $db); // todo: check db name
    }

    public function testGetNonDefaultDatabase() {
        $mongo = CM_Services::getInstance()->getMongoDB('test');
        $db = $mongo->getDatabase();
        $this->assertInstanceOf('MongoDB', $db); // todo: check db name
    }
}
