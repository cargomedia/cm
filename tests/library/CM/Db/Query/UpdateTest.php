<?php

class CM_Db_Query_UpdateTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_Update('t`est', array('f`oo' => 2, 'b`ar' => null), array('f`oo' => '1'));
		$this->assertSame('UPDATE `t``est` SET `f``oo` = ?, `b``ar` = NULL WHERE `f``oo` = ?', $query->getSqlTemplate());
		$this->assertEquals(array(2, '1'), $query->getParameters());
	}

	public function testWithoutWhere() {
		$query = new CM_Db_Query_Update('t`est', array('f`oo' => 'bar', 'b`ar' => null));
		$this->assertSame('UPDATE `t``est` SET `f``oo` = ?, `b``ar` = NULL', $query->getSqlTemplate());
		$this->assertEquals(array('bar'), $query->getParameters());
	}
}
