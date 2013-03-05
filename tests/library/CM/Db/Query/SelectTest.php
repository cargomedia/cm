<?php

class CM_Db_Query_SelectTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_Select('t`est', array('f`oo', 'bar'), array('foo' => 'foo1'), 'order1');
		$this->assertSame('SELECT `f``oo`,`bar` FROM `t``est` WHERE `foo` = ? ORDER BY order1', $query->getSqlTemplate());
	}

	public function testSingleField() {
		$query = new CM_Db_Query_Select('test', 'foo');
		$this->assertSame('SELECT `foo` FROM `test`', $query->getSqlTemplate());
	}
}
