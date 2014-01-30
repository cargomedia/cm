<?php

class CM_Db_Query_SelectMultipleTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function testMultipleWhere() {
		$query = new CM_Db_Query_SelectMultiple(self::$_client, 't`est', array('f`oo', 'bar'), array(array('foo' => 'foo1', 'bar' => null),
			array('foo2' => 'foo2')), 'order1');
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ? AND `bar` IS NULL OR `foo2` = ? ORDER BY order1', $query->getSqlTemplate());
		$this->assertSame(array('foo1', 'foo2'), $query->getParameters());
	}

	public function testSingleWhere() {
		$query = new CM_Db_Query_SelectMultiple(self::$_client, 't`est', array('f`oo', 'bar'), array(array('foo' => 'foo1', 'bar' => null)));
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ? AND `bar` IS NULL', $query->getSqlTemplate());
		$this->assertSame(array('foo1'), $query->getParameters());
	}

	public function testEmptyWhere() {
		$query = new CM_Db_Query_SelectMultiple(self::$_client, 't`est', array('f`oo', 'bar'), array(), 'order1');
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` ORDER BY order1', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testCommonParts() {
		$query = new CM_Db_Query_SelectMultiple(self::$_client, 'test', array('foo', 'bar'), array(
			array('foo' => 'foo1', 'bar' => null, 'poo' => 'poo1'),
			array('foo' => 'foo1', 'bar' => 'bar1', 'poo' => 'poo1'),
			array('foo' => 'foo1', 'bar' => 'bar2', 'poo' => 'poo1')
		), 'order1');
		$this->assertSame('SELECT `foo`,`bar` FROM `test` WHERE (`bar` IS NULL OR `bar` = ? OR `bar` = ?) AND `foo` = ? AND `poo` = ? ORDER BY order1', $query->getSqlTemplate());
		$this->assertSame(array('bar1', 'bar2', 'foo1', 'poo1'), $query->getParameters());
	}

	public function testAllCommonWhere() {
		$query = new CM_Db_Query_SelectMultiple(self::$_client, 't`est', array('f`oo', 'bar'), array(array('foo' => 'foo1'), array('foo' => 'foo1'),
			array('foo' => 'foo1')));
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ?', $query->getSqlTemplate());
		$this->assertSame(array('foo1'), $query->getParameters());

		$query = new CM_Db_Query_SelectMultiple(self::$_client, 't`est', array('f`oo', 'bar'), array(
			array('foo' => 'foo1', 'poo' => 'poo1'),
			array('foo' => 'foo1', 'poo' => 'poo1'),
		));
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ? AND `poo` = ?', $query->getSqlTemplate());
		$this->assertSame(array('foo1', 'poo1'), $query->getParameters());
	}
}
