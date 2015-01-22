<?php

class CM_Model_StorageAdapter_DatabaseTest extends CMTest_TestCase {

    public static function setupBeforeClass() {
        CM_Db_Db::exec("CREATE TABLE `mock_modelStorageAdapter` (
				`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				`foo` VARCHAR(32),
				`bar` INT
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        CM_Db_Db::exec("DROP TABLE `mock_modelStorageAdapter`");
    }

    protected function tearDown() {
        CM_Db_Db::truncate('mock_modelStorageAdapter');
    }

    public function testGetTableName() {
        CM_Config::get()->CM_Model_Abstract = new stdClass();
        CM_Config::get()->CM_Model_Abstract->types = array(
            1 => 'CMTest_ModelMock_1',
            2 => 'CMTest_ModelMock_2',
        );

        $adapter = new CM_Model_StorageAdapter_Database();
        $method = CMTest_TH::getProtectedMethod('CM_Model_StorageAdapter_Database', '_getTableName');
        $this->assertSame('cmtest_modelmock_1', $method->invoke($adapter, 1));
        $this->assertSame('custom_table', $method->invoke($adapter, 2));
    }

    public function testLoad() {
        $id1 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo1', 'bar' => 1));
        $id2 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo2', 'bar' => 2));
        $type = 99;

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(array('_getTableName'))->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $this->assertSame(array('id' => $id1, 'foo' => 'foo1', 'bar' => '1'), $adapter->load($type, array('id' => $id1)));
        $this->assertSame(array('id' => $id1, 'foo' => 'foo1', 'bar' => '1'), $adapter->load($type, array('id' => $id1, 'foo' => 'foo1')));
        $this->assertSame(array('id' => $id2, 'foo' => 'foo2', 'bar' => '2'), $adapter->load($type, array('id' => $id2)));
        $this->assertFalse($adapter->load($type, array('id' => '9999')));
        $this->assertFalse($adapter->load($type, array('id' => $id1, 'foo' => '9999')));
    }

    public function testSave() {
        $id1 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo1', 'bar' => 1));
        $id2 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo2', 'bar' => 2));
        $type = 99;

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(array('_getTableName'))->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $adapter->save($type, array('id' => $id1), array('foo' => 'hello', 'bar' => 55));
        $this->assertRow('mock_modelStorageAdapter', array('id' => $id1, 'foo' => 'hello', 'bar' => '55'));
        $this->assertRow('mock_modelStorageAdapter', array('id' => $id2, 'foo' => 'foo2', 'bar' => '2'));

        $adapter->save($type, array('id' => $id1, 'foo' => '9999'), array('foo' => 'world', 'bar' => 66));
        $this->assertNotRow('mock_modelStorageAdapter', array('id' => $id1, 'foo' => 'world', 'bar' => '66'));
    }

    public function testCreate() {
        $type = 99;

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(array('_getTableName'))->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $id = $adapter->create($type, array('foo' => 'foo1', 'bar' => 23));
        $this->assertInternalType('array', $id);
        $this->assertCount(1, $id);
        $this->assertArrayHasKey('id', $id);
        $this->assertInternalType('int', $id['id']);
        $this->assertRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));
    }

    public function testDelete() {
        $type = 99;

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(array('_getTableName'))->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $id = $adapter->create($type, array('foo' => 'foo1', 'bar' => 23));
        $this->assertRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));

        $adapter->delete($type, $id);
        $this->assertNotRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));
    }

    public function testLoadMultiple() {
        $id1 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo1', 'bar' => 1]);
        $id2 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo2', 'bar' => 2]);
        $id3 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo3', 'bar' => 3]);
        $id4 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo4', 'bar' => 4]);
        $id5 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo5', 'bar' => 5]);
        $id6 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo6', 'bar' => 6]);
        $id7 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo7', 'bar' => 7]);
        $id8 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo8', 'bar' => 8]);
        $id9 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo9', 'bar' => 9]);
        $id10 = CM_Db_Db::insert('mock_modelStorageAdapter', ['foo' => 'foo10', 'bar' => 10]);

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(['_getTableName'])->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $idsTypes = [
            1      => ['type' => 1, 'id' => ['id' => $id1]],
            '2'    => ['type' => 2, 'id' => ['id' => $id3]],
            'foo'  => ['type' => 2, 'id' => ['id' => $id8]],
            'bar'  => ['type' => 3, 'id' => ['id' => $id10]],
            'foo2' => ['type' => 2, 'id' => ['id' => $id8]],
        ];
        $expected = [
            1      => ['id' => $id1, 'foo' => 'foo1', 'bar' => '1'],
            '2'    => ['id' => $id3, 'foo' => 'foo3', 'bar' => '3'],
            'foo'  => ['id' => $id8, 'foo' => 'foo8', 'bar' => '8'],
            'foo2' => ['id' => $id8, 'foo' => 'foo8', 'bar' => '8'],
            'bar'  => ['id' => $id10, 'foo' => 'foo10', 'bar' => '10'],
        ];

        $values = $adapter->loadMultiple($idsTypes);
        $this->assertSame(5, count($values));
        $this->assertSame($expected, $values);
    }

    public function testFindByData() {
        $id1 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo1', 'bar' => 1));
        $id2 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo2', 'bar' => 2));
        $type = 99;

        $adapter = $this->getMockBuilder('CM_Model_StorageAdapter_Database')->setMethods(array('_getTableName'))->getMock();
        $adapter->expects($this->any())->method('_getTableName')->will($this->returnValue('mock_modelStorageAdapter'));
        /** @var CM_Model_StorageAdapter_Database $adapter */

        $this->assertSame(array('id' => $id1), $adapter->findByData($type, array('foo' => 'foo1')));
        $this->assertSame(array('id' => $id1), $adapter->findByData($type, array('bar' => 1)));
        $this->assertSame(array('id' => $id1), $adapter->findByData($type, array('foo' => 'foo1', 'bar' => 1)));
        $this->assertNull($adapter->findByData($type, array('foo' => 'foo2', 'bar' => 1)));
    }
}

class CMTest_ModelMock_1 extends CM_Model_Abstract {

}

class CMTest_ModelMock_2 extends CM_Model_Abstract {

    public static function getTableName() {
        return 'custom_table';
    }
}
