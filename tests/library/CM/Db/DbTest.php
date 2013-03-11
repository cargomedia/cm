<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Db_Db::exec(
			'CREATE TABLE `test` (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`foo` VARCHAR(100) NOT NULL,
					`bar` VARCHAR(100) NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY (`foo`)
				)');
	}

	public static function tearDownAfterClass() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testGetRandIdEmpty() {
		try {
			CM_Db_Db::getRandId('test', 'id');
			$this->fail();
		} catch (CM_DB_Exception $e) {
			$this->assertContains('Cannot find random id', $e->getMessage());
		}
	}

	public function testGetRandId() {
		CM_Db_Db::insert('test', array('foo', 'bar'), array(array('foo1', 'bar1'), array('foo2', 'bar2'), array('foo3', 'bar3')));
		$id = CM_Db_Db::getRandId('test', 'id');
		$this->assertGreaterThanOrEqual(1, $id);

		$id = CM_Db_Db::getRandId('test', 'id', '`id` = 2');
		$this->assertEquals(2, $id);
	}
}
