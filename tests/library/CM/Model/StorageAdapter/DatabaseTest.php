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

	public function testLoad() {
		$id1 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo1', 'bar' => 1));
		$id2 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo2', 'bar' => 2));

		$adapter = new CM_Model_StorageAdapter_Database('mock_modelStorageAdapter');
		$this->assertSame(array('id' => $id1, 'foo' => 'foo1', 'bar' => '1'), $adapter->load(array('id' => $id1)));
		$this->assertSame(array('id' => $id1, 'foo' => 'foo1', 'bar' => '1'), $adapter->load(array('id' => $id1, 'foo' => 'foo1')));
		$this->assertSame(array('id' => $id2, 'foo' => 'foo2', 'bar' => '2'), $adapter->load(array('id' => $id2)));
		$this->assertFalse($adapter->load(array('id' => '9999')));
		$this->assertFalse($adapter->load(array('id' => $id1, 'foo' => '9999')));
	}

	public function testSave() {
		$id1 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo1', 'bar' => 1));
		$id2 = CM_Db_Db::insert('mock_modelStorageAdapter', array('foo' => 'foo2', 'bar' => 2));

		$adapter = new CM_Model_StorageAdapter_Database('mock_modelStorageAdapter');

		$adapter->save(array('id' => $id1), array('foo' => 'hello', 'bar' => 55));
		$this->assertRow('mock_modelStorageAdapter', array('id' => $id1, 'foo' => 'hello', 'bar' => '55'));
		$this->assertRow('mock_modelStorageAdapter', array('id' => $id2, 'foo' => 'foo2', 'bar' => '2'));

		$adapter->save(array('id' => $id1, 'foo' => '9999'), array('foo' => 'world', 'bar' => 66));
		$this->assertNotRow('mock_modelStorageAdapter', array('id' => $id1, 'foo' => 'world', 'bar' => '66'));
	}

	public function testCreate() {
		$adapter = new CM_Model_StorageAdapter_Database('mock_modelStorageAdapter');

		$id = $adapter->create(array('foo' => 'foo1', 'bar' => 23));
		$this->assertInternalType('array', $id);
		$this->assertCount(1, $id);
		$this->assertArrayHasKey('id', $id);
		$this->assertRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));
	}

	public function testDelete() {
		$adapter = new CM_Model_StorageAdapter_Database('mock_modelStorageAdapter');

		$id = $adapter->create(array('foo' => 'foo1', 'bar' => 23));
		$this->assertRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));

		$adapter->delete($id);
		$this->assertNotRow('mock_modelStorageAdapter', array('id' => $id['id'], 'foo' => 'foo1', 'bar' => '23'));
	}
}
