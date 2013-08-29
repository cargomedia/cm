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
		$this->assertSame('cmtest_modelmock_2', $method->invoke($adapter, 2));

		CMTest_TH::clearConfig();
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
}
