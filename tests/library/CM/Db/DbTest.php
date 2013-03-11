<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testDescribeColumn() {
		CM_Db_Db::exec('
			CREATE TABLE `cm_db_db_describe_column_test` (
				`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			)');
		$column = CM_Db_Db::describeColumn('cm_db_db_describe_column_test', 'id');
		$this->assertSame('id', $column->getName());
		$this->assertSame('int', $column->getType());
		$this->assertSame(12, $column->getSize());
		$this->assertNull($column->getEnum());
		$this->assertTrue($column->getUnsigned());
		$this->assertFalse($column->getAllowNull());
		$this->assertNull($column->getDefaultValue());
	}

	public function testDescribeColumnThrowsException() {
		try {
			CM_Db_Db::describeColumn('cm_db_db_describe_column_test', 'id1');
			$this->fail("Column doesn't exist");
		} catch (CM_Db_Exception $e) {
			$this->assertTrue(true);
		}

		try {
			CM_Db_Db::describeColumn('cm_db_db_describe_column_test1', 'id');
			$this->fail("Table doesn't exist");
		} catch (CM_Db_Exception $e) {
			$this->assertTrue(true);
		}
	}
}
