<?php

class CM_Db_Query_AbstractTest extends CMTest_TestCase {

	/** @var CM_Db_Client */
	private static $_client;

	public static function setUpBeforeClass() {
		$config = CM_Config::get()->CM_Db_Db;
		self::$_client = new CM_Db_Client($config->server['host'], $config->server['port'], $config->username, $config->password, $config->db);
	}

	public function testWhereNull() {
		$query = new CM_Db_Query_AbstractMockWhere(self::$_client, null);
		$this->assertSame('', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testWhereString() {
		$query = new CM_Db_Query_AbstractMockWhere(self::$_client, 'hello world');
		$this->assertSame('WHERE hello world', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testWhereArray() {
		$query = new CM_Db_Query_AbstractMockWhere(self::$_client, array('foo' => 'foo1', 'bar' => 2));
		$this->assertSame('WHERE `foo` = ? AND `bar` = ?', $query->getSqlTemplate());
		$this->assertSame(array('foo1', 2), $query->getParameters());
	}

	public function testOrderByNull() {
		$query = new CM_Db_Query_AbstractMockOrderBy(self::$_client, null);
		$this->assertSame('', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testOrderByString() {
		$query = new CM_Db_Query_AbstractMockOrderBy(self::$_client, 'hello world');
		$this->assertSame('ORDER BY hello world', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testOrderByArray() {
		$query = new CM_Db_Query_AbstractMockOrderBy(self::$_client, array('foo' => 'ASC', 'bar' => 'DESC'));
		$this->assertSame('ORDER BY `foo` ASC, `bar` DESC', $query->getSqlTemplate());
		$this->assertSame(array(), $query->getParameters());
	}

	public function testOrderByArrayInvalidDirection() {
		try {
			new CM_Db_Query_AbstractMockOrderBy(self::$_client, array('foo' => 'NONEXISTENT'));
			$this->fail();
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('Invalid order direction', $e->getMessage());
		}
	}

	public function testOrderByArrayInvalidField() {
		try {
			new CM_Db_Query_AbstractMockOrderBy(self::$_client, array('foo'));
			$this->fail();
		} catch (CM_Exception_Invalid $e) {
			$this->assertContains('Order field name is not string', $e->getMessage());
		}
	}
}

class CM_Db_Query_AbstractMockWhere extends CM_Db_Query_Abstract {

	public function __construct($client, $where) {
		parent::__construct($client);
		$this->_addWhere($where);
	}
}

class CM_Db_Query_AbstractMockOrderBy extends CM_Db_Query_Abstract {

	public function __construct($client, $orderBy) {
		parent::__construct($client);
		$this->_addOrderBy($orderBy);
	}
}
