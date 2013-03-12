<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE IF EXISTS `test`');
		CMTest_TH::clearEnv();
	}

	public function testGetRandIdEmpty() {
		CM_Db_Db::exec('CREATE TABLE `test` (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`id`)
				)');
		try {
			CM_Db_Db::getRandId('test', 'id');
			$this->fail();
		} catch (CM_DB_Exception $e) {
			$this->assertContains('Cannot find random id', $e->getMessage());
		}
	}

	public function testGetRandId() {
		CM_Db_Db::exec('CREATE TABLE `test` (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`foo` VARCHAR(100) NOT NULL,
					`bar` VARCHAR(100) NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY (`foo`)
				)');
		CM_Db_Db::insert('test', array('foo', 'bar'), array(array('foo1', 'bar1'), array('foo2', 'bar2'), array('foo3', 'bar3')));
		$id = CM_Db_Db::getRandId('test', 'id');
		$this->assertGreaterThanOrEqual(1, $id);

		$id = CM_Db_Db::getRandId('test', 'id', '`id` = 2');
		$this->assertEquals(2, $id);
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
			$this->assertContains('id1', $e->getMessage());
		}

		try {
			CM_Db_Db::describeColumn('cm_db_db_describe_column_test1', 'id');
			$this->fail("Table doesn't exist");
		} catch (CM_Db_Exception $e) {
			$this->assertContains('id', $e->getMessage());
		}
	}
}
