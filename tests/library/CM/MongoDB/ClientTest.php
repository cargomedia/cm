<?php

class CM_MongoDB_ClientTest extends CMTest_TestCase {

    private $_collectionPrefix = 'UnitTest_';

    private function getCollectionName($testName) {
        return $this->_collectionPrefix . $testName;
    }

    public function testInsert() {
        $mdb = CM_MongoDB_Client::getInstance();
        $collectionName = $this->getCollectionName('insert');
        $name = 'Bob';
        $mdb->insert($collectionName, array('userId' => 123, 'name' => $name));
        $res = $mdb->findOne($collectionName, array('userId' => 123));
        $this->assertSame($res['name'], $name);
    }
}
