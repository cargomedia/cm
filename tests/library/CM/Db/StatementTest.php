<?php

class CM_Db_StatementTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);

		CM_Db_Db::exec('CREATE TABLE `test` (`id` INT(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))');
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testExecute() {
		$statement = self::$_client->createStatement('SELECT `id` FROM `test`');
		$this->assertInstanceOf('CM_Db_Result', $statement->execute());

		$statement = self::$_client->createStatement('SELECT `id` FROM `test` WHERE `id`=?');
		$this->assertInstanceOf('CM_Db_Result', $statement->execute(array(12)));
	}

	public function testExecuteInvalidTable() {
		$statement = self::$_client->createStatement('SELECT `id` FROM `nonexistent`');
		try {
			$this->assertInstanceOf('CM_Db_Result', $statement->execute());
			$this->fail('Could select from nonexistent table');
		} catch (CM_Db_Exception $e) {
			$this->assertContains('Cannot execute statement', $e->getMessage());
		}
	}

	public function testExecuteInvalidSql() {
		$statement = self::$_client->createStatement('INVALID QUERY');
		try {
			$this->assertInstanceOf('CM_Db_Result', $statement->execute());
			$this->fail('Could select from nonexistent table');
		} catch (CM_Db_Exception $e) {
			$this->assertContains('Cannot execute statement', $e->getMessage());
		}
	}
}
