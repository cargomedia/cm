<?php

class CM_Db_Query_ExistsColumnTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_ExistsColumn('t`est', 'b`ar');
		$this->assertSame("SHOW COLUMNS FROM `t``est` LIKE ?", $query->getSqlTemplate());
		$this->assertEquals(array('b`ar'), $query->getParameters());
	}
}
