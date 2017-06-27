<?php

class CM_Model_StorageAdapter_MongoDbTest extends CMTest_TestCase {

    public static function setupBeforeClass() {
        CMTest_TH::getMongoDb()->createCollection('mock_modelStorageAdapter');
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        CMTest_TH::getMongoDb()->drop('mock_modelStorageAdapter');
    }

    protected function tearDown() {
        CMTest_TH::getMongoDb()->deleteMany('mock_modelStorageAdapter');
    }

    public function testGetCollectionName() {
        CM_Config::get()->CM_Model_Abstract->types += [
            1 => 'CMTest_ModelMock_3',
            2 => 'CMTest_ModelMock_4',
        ];

        $adapter = new CM_Model_StorageAdapter_MongoDb();
        $method = CMTest_TH::getProtectedMethod('CM_Model_StorageAdapter_MongoDb', '_getCollectionName');
        $this->assertSame('cmtest_modelmock_3', $method->invoke($adapter, 1));
        $this->assertSame('custom_table', $method->invoke($adapter, 2));
    }

    public function testLoad() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $id2 = $adapter->create($type, ['foo' => 'foo2', 'bar' => 2]);

        $this->assertEquals(['_id' => $id1['id'], '_type' => 99, 'foo' => 'foo1', 'bar' => 1], $adapter->load($type, $id1));
        $this->assertEquals(['_id' => $id2['id'], '_type' => 99, 'foo' => 'foo2', 'bar' => 2], $adapter->load($type, $id2));

        $this->assertSame(false, $adapter->load($type, ['id' => '234']));
    }

    public function testLoad_nonExistent() {
        $type = 99;
        $adapter = $this->_getAdapter();

        $this->assertFalse($adapter->load($type, ['id' => CM_MongoDb_Client::getObjectId()]));
    }

    public function testSave() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $adapter->create($type, ['foo' => 'foo2', 'bar' => 2]);
        $adapter->save($type, $id1, ['foo' => 'hello', 'bar' => 55]);

        $this->assertSame(2, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter'));
        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'hello', 'bar' => 55]));
        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'foo2', 'bar' => 2]));
    }

    public function testSave_nonExistent() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $adapter->save($type, ['id' => CM_MongoDb_Client::getObjectId()], ['foo' => 'foo2', 'bar' => 2]);

        $this->assertSame(1, $mongoDb->count('mock_modelStorageAdapter'));
        $this->assertSame(1, $mongoDb->count('mock_modelStorageAdapter', ['foo' => 'foo1', 'bar' => 1]));
    }

    public function testCreate() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id = $adapter->create($type, ['foo' => 'foo1', 'bar' => 23]);

        $this->assertInternalType('array', $id);
        $this->assertCount(1, $id);
        $this->assertArrayHasKey('id', $id);
        $this->assertInternalType('string', $id['id']);

        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['_type' => 99, 'foo' => 'foo1', 'bar' => 23]));
    }

    public function testDelete() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $id2 = $adapter->create($type, ['foo' => 'foo2', 'bar' => 2]);

        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'foo1', 'bar' => 1]));
        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'foo2', 'bar' => 2]));
        $adapter->delete($type, $id1);
        $this->assertSame(0, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'foo1', 'bar' => 1]));
        $this->assertSame(1, CMTest_TH::getMongoDb()->count('mock_modelStorageAdapter', ['foo' => 'foo2', 'bar' => 2]));
    }

    public function testLoadMultiple() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $id2 = $adapter->create($type, ['foo' => 'foo2', 'bar' => 2]);
        $id3 = $adapter->create($type, ['foo' => 'foo3', 'bar' => 3]);
        $id4 = $adapter->create($type, ['foo' => 'foo4', 'bar' => 4]);
        $id5 = $adapter->create($type, ['foo' => 'foo5', 'bar' => 5]);
        $id6 = $adapter->create($type, ['foo' => 'foo6', 'bar' => 6]);
        $id7 = $adapter->create($type, ['foo' => 'foo7', 'bar' => 7]);
        $id8 = $adapter->create($type, ['foo' => 'foo8', 'bar' => 8]);
        $id9 = $adapter->create($type, ['foo' => 'foo9', 'bar' => 9]);
        $id10 = $adapter->create($type, ['foo' => 'foo10', 'bar' => 10]);

        $idsTypes = [
            1      => ['type' => $type, 'id' => $id1],
            '2'    => ['type' => $type, 'id' => $id3],
            'foo'  => ['type' => $type, 'id' => $id10],
            'bar'  => ['type' => $type, 'id' => $id8],
            'foo2' => ['type' => $type, 'id' => $id10],
        ];
        $expected = [
            1      => ['_id' => $id1['id'], '_type' => $type, 'foo' => 'foo1', 'bar' => 1],
            '2'    => ['_id' => $id3['id'], '_type' => $type, 'foo' => 'foo3', 'bar' => 3],
            'bar'  => ['_id' => $id8['id'], '_type' => $type, 'foo' => 'foo8', 'bar' => 8],
            'foo'  => ['_id' => $id10['id'], '_type' => $type, 'foo' => 'foo10', 'bar' => 10],
            'foo2' => ['_id' => $id10['id'], '_type' => $type, 'foo' => 'foo10', 'bar' => 10],
        ];

        $values = $adapter->loadMultiple($idsTypes);
        $this->assertSame(5, count($values));
        $this->assertEquals($expected, $values);
    }

    public function testFindByData() {
        $type = 99;
        $adapter = $this->_getAdapter();
        $id1 = $adapter->create($type, ['foo' => 'foo1', 'bar' => 1]);
        $id2 = $adapter->create($type, ['foo' => 'foo2', 'bar' => 2]);

        $this->assertSame($id1, $adapter->findByData($type, array('foo' => 'foo1')));
        $this->assertSame($id1, $adapter->findByData($type, array('bar' => 1)));
        $this->assertSame($id1, $adapter->findByData($type, array('foo' => 'foo1', 'bar' => 1)));
        $this->assertSame($id2, $adapter->findByData($type, array('foo' => 'foo2')));
        $this->assertNull($adapter->findByData($type, array('foo' => 'foo2', 'bar' => 1)));
    }

    /**
     * @return CM_Model_StorageAdapter_MongoDb
     */
    protected function _getAdapter() {
        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_MongoDb')->setMethods(['_getCollectionName'])->getMock();
        $adapter->expects($this->any())->method('_getCollectionName')->will($this->returnValue('mock_modelStorageAdapter'));
        return $adapter;
    }
}

class CMTest_ModelMock_3 extends CM_Model_Abstract {

}

class CMTest_ModelMock_4 extends CM_Model_Abstract {

    public static function getTableName() {
        return 'custom_table';
    }
}
