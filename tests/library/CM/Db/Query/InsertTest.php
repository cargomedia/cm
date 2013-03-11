<?php

class CM_Db_Query_InsertTest extends CMTest_TestCase {

	public function testAll() {
		// Null value insertion
		$query = new CM_Db_Query_Insert('t`est', array('bar' => null));
		$this->assertSame('INSERT INTO `t``est` (`bar`) VALUES (NULL)', $query->getSqlTemplate());
		$this->assertEquals(array(), $query->getParameters());

		// Associative array
		$query = new CM_Db_Query_Insert('t`est', array('foo' => 'foo1'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo1'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo' => 'foo2', 'bar' => 'bar2'));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo2', 'bar2'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo' => 'foo2', 'bar' => 'bar2'), null, array('foo' => 'fooX'));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?) ON DUPLICATE KEY UPDATE `foo` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo2', 'bar2', 'fooX'), $query->getParameters());

		// Scalar and array
		$query = new CM_Db_Query_Insert('t`est', 'foo', 'foo3');
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo3'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', 'foo', array('foo4', 'foo5'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo4', 'foo5'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', 'foo', array('foo4', 'foo5'), array('foo' => 'fooX'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?) ON DUPLICATE KEY UPDATE `foo` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo4', 'foo5', 'fooX'), $query->getParameters());

		// Array and array
		$query = new CM_Db_Query_Insert('t`est', array('foo'), array('foo6'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo6'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo'), array('foo7', 'foo8'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo7', 'foo8'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo', 'bar'), array('foo9', 'bar9'));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo9', 'bar9'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo', 'bar'), array('foo9', 'bar9'), array('foo' => 'fooX','bar' => 'barX'));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?) ON DUPLICATE KEY UPDATE `foo` = ?,`bar` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo9', 'bar9', 'fooX', 'barX'), $query->getParameters());

		// Scalar and array of array
		$query = new CM_Db_Query_Insert('t`est', 'foo', array(array('foo10')));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo10'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', 'foo', array(array('foo11'), array('foo12')));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo11', 'foo12'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', 'foo', array(array('foo11'), array('foo12')), array('foo' => 'fooX'));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?) ON DUPLICATE KEY UPDATE `foo` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo11', 'foo12', 'fooX'), $query->getParameters());

		// Array and array of array
		$query = new CM_Db_Query_Insert('t`est', array('foo'), array(array('foo13')));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo13'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo'), array(array('foo14'), array('foo15')));
		$this->assertSame('INSERT INTO `t``est` (`foo`) VALUES (?),(?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo14', 'foo15'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo', 'bar'), array(array('foo16', 'bar16')));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo16', 'bar16'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo', 'bar'), array(array('foo17', 'bar17'), array('foo18', 'bar18')));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?),(?,?)', $query->getSqlTemplate());
		$this->assertEquals(array('foo17', 'bar17', 'foo18', 'bar18'), $query->getParameters());
		$query = new CM_Db_Query_Insert('t`est', array('foo', 'bar'), array(array('foo17', 'bar17'),
			array('foo18', 'bar18')), array('foo' => 'fooX'));
		$this->assertSame('INSERT INTO `t``est` (`foo`,`bar`) VALUES (?,?),(?,?) ON DUPLICATE KEY UPDATE `foo` = ?', $query->getSqlTemplate());
		$this->assertEquals(array('foo17', 'bar17', 'foo18', 'bar18', 'fooX'), $query->getParameters());

		// Statement
		$query = new CM_Db_Query_Insert('t`est', array('foo' => 'foo2', 'bar' => 'bar2'), null, null, 'INSERT IGNORE');
		$this->assertSame('INSERT IGNORE INTO `t``est` (`foo`,`bar`) VALUES (?,?)', $query->getSqlTemplate());
	}
}
