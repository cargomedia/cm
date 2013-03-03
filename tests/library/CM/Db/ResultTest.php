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

		CM_Db_Db::exec('INSERT INTO `test` (`foo`, `bar`) VALUES("foo1", "bar1")');
		CM_Db_Db::exec('INSERT INTO `test` (`foo`, `bar`) VALUES("foo2", "bar2")');
		CM_Db_Db::exec('INSERT INTO `test` (`foo`, `bar`) VALUES("foo3", "bar3")');
	}

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testFetchAssoc() {
		$result = self::$_client->createStatement('SELECT * FROM `test`')->execute();
		$this->assertSame(array('id' => '1', 'foo' => 'foo1', 'bar' => 'bar1'), $result->fetchAssoc());
		$this->assertSame(array('id' => '2', 'foo' => 'foo2', 'bar' => 'bar2'), $result->fetchAssoc());
		$this->assertSame(array('id' => '3', 'foo' => 'foo3', 'bar' => 'bar3'), $result->fetchAssoc());
		$this->assertSame(false, $result->fetchAssoc());
	}

	public function testFetchOne() {
		$result = self::$_client->createStatement('SELECT `bar` FROM `test`')->execute();
		$this->assertSame('bar1', $result->fetchOne());
		$this->assertSame('bar2', $result->fetchOne());
		$this->assertSame('bar3', $result->fetchOne());
		$this->assertSame(false, $result->fetchOne());
	}

	public function testFetchOneMultipleColumns() {
		$result = self::$_client->createStatement('SELECT * FROM `test` WHERE `foo`="foo2"')->execute();
		$this->assertSame('2', $result->fetchOne());
	}

	public function testFetchCol() {
		$result = self::$_client->createStatement('SELECT `bar` FROM `test`')->execute();
		$this->assertSame(array('bar1', 'bar2', 'bar3'), $result->fetchCol());
		$this->assertSame(array(), $result->fetchCol());
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
		$this->assertSame(array('bar' => 'bar2'), $result->fetchAssoc());
	}
}
