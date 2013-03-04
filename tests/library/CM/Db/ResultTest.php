<?php

class CM_Db_ResultTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function setUp() {
		CM_Db_Db::exec(
			'CREATE TABLE `test` (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`foo` VARCHAR(100) NOT NULL,
					`bar` VARCHAR(100) NULL,
					PRIMARY KEY (`id`)
				)');

		CM_Db_Db::exec('INSERT INTO `test` (`foo`, `bar`) VALUES("foo1", "bar1"),("foo2", "bar2"),("foo3", "bar3")');
	}

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testFetch() {
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame(array('id' => '1', 'foo' => 'foo1', 'bar' => 'bar1'), $result->fetch());
		$this->assertSame(array('id' => '2', 'foo' => 'foo2', 'bar' => 'bar2'), $result->fetch());
		$this->assertSame(array('id' => '3', 'foo' => 'foo3', 'bar' => 'bar3'), $result->fetch());
		$this->assertSame(false, $result->fetch());
	}

	public function testFetchColumn() {
		$result = self::$_client->createStatement('SELECT `bar` FROM `test`')->execute();
		$this->assertSame('bar1', $result->fetchColumn());
		$this->assertSame('bar2', $result->fetchColumn());
		$this->assertSame('bar3', $result->fetchColumn());
		$this->assertSame(false, $result->fetchColumn());
	}

	public function testFetchColumnIndex() {
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame('1', $result->fetchColumn(0));
		$this->assertSame('foo2', $result->fetchColumn(1));
		$this->assertSame('bar3', $result->fetchColumn(2));
	}

	public function testFetchAllColumn() {
		$result = self::$_client->createStatement('SELECT `bar` FROM `test`')->execute();
		$this->assertSame(array('bar1', 'bar2', 'bar3'), $result->fetchAllColumn());
		$this->assertSame(array(), $result->fetchAllColumn());
	}

	public function testFetchAllColumnIndex() {
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame(array('foo1', 'foo2', 'foo3'), $result->fetchAllColumn(1));
		$this->assertSame(array(), $result->fetchAllColumn());
	}

	public function testFetchAll() {
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame(array(
			array('id' => '1', 'foo' => 'foo1', 'bar' => 'bar1'),
			array('id' => '2', 'foo' => 'foo2', 'bar' => 'bar2'),
			array('id' => '3', 'foo' => 'foo3', 'bar' => 'bar3'),
		), $result->fetchAll());
		$this->assertSame(array(), $result->fetchAll());
	}

	public function testGetAffectedRows() {
		$result = self::$_client->createStatement('DELETE FROM `test` WHERE `foo`="foo1" OR `foo`="foo2"')->execute();
		$this->assertSame(2, $result->getAffectedRows());
	}

	public function testGetAffectedRowsForSelect() {
		// True with mysql-driver
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame(3, $result->getAffectedRows());
	}

	public function testFetchNotFetchedResult() {
		self::$_client->createStatement('SELECT `bar` FROM `test` WHERE `foo`="foo1"')->execute();
		$result = self::$_client->createStatement('SELECT `bar` FROM `test` WHERE `foo`="foo2"')->execute();
		$this->assertSame(array('bar' => 'bar2'), $result->fetch());
	}
}
