<?php

class CM_Db_Query_ExistsTableTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_ExistsTable('t`est');
		$this->assertSame("SHOW TABLES LIKE ?", $query->getSqlTemplate());
		$this->assertEquals(array('t`est'), $query->getParameters());
	}
}
