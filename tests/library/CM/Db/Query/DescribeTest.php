<?php

class CM_Db_Query_DescribeTest extends CMTest_TestCase {

	public function testWithColumn() {
		$query = new CM_Db_Query_Describe(CMTest_TH::getDbClient(), 't`est table', 't`est column');
		$this->assertSame('DESCRIBE `t``est table` `t``est column`', $query->getSqlTemplate());
	}

	public function testWithoutColumn() {
		$query = new CM_Db_Query_Describe(CMTest_TH::getDbClient(), 't`est table');
		$this->assertSame('DESCRIBE `t``est table`', $query->getSqlTemplate());
	}
}
