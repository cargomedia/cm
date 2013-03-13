<?php

class CM_Db_Query_SelectTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function testAll() {
		$query = new CM_Db_Query_Select(self::$_client, 't`est', array('f`oo', 'bar'), array('foo' => 'foo1'), 'order1');
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ? ORDER BY order1', $query->getSqlTemplate());
	}

	public function testSingleField() {
		$query = new CM_Db_Query_Select(self::$_client, 'test', 'foo');
		$this->assertSame('SELECT `foo` FROM `test`', $query->getSqlTemplate());
	}
}
