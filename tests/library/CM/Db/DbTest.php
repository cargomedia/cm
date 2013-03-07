<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public function setUp() {
		CM_Db_Db::exec(
			'CREATE TABLE `test` (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`foo` VARCHAR(100) NOT NULL,
					`bar` VARCHAR(100) NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY (`foo`)
				)');
	}

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testExistsTable() {
		$this->assertSame(true, CM_Db_Db::existsTable('test'));
		$this->assertSame(false, CM_Db_Db::existsTable('foo'));
	}
}
