<?php

class CM_Db_Query_ExistsIndexTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_ExistsIndex('t`est', 'b`ar');
		$this->assertSame("SHOW INDEX FROM `t``est` WHERE Key_name = ?", $query->getSqlTemplate());
		$this->assertEquals(array('b`ar'), $query->getParameters());
	}
}
