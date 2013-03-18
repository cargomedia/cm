<?php

class CM_Db_Query_CountTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function testAll() {
		$query = new CM_Db_Query_Count(self::$_client, 't`est', array('foo' => 'foo1', 'bar' => 'bar1'));
		$this->assertSame('SELECT COUNT(*) FROM `t``est` WHERE `foo` = ? AND `bar` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo1', 'bar1'), $query->getParameters());
	}
}
