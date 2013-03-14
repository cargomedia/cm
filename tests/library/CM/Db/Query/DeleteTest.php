<?php

class CM_Db_Query_DeleteTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function testDelete() {
		$query = new CM_Db_Query_Delete(self::$_client, 't`est', array('foo' => 'foo1'));
		$this->assertSame('DELETE FROM `t``est` WHERE `foo` = ?', $query->getSqlTemplate());
	}
}
