<?php

class CM_Db_Query_TruncateTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_Truncate('t`est');
		$this->assertSame('TRUNCATE TABLE `t``est`', $query->getSqlTemplate());
	}
}
