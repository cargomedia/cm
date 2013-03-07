<?php

class CM_Db_Query_DeleteTest extends CMTest_TestCase {

	public function testDelete() {
		$query = new CM_Db_Query_Delete('t`est', array('foo' => 'foo1'));
		$this->assertSame('DELETE FROM `t``est` WHERE `foo` = ?', $query->getSqlTemplate());
	}
}
