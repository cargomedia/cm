<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_MysqlResultTest extends TestCase {

	public static function setUpBeforeClass() {
		define('TBL_TEST', 'test');
		CM_Mysql::exec(
				'CREATE TABLE TBL_TEST (
					`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
					`foo` VARCHAR(100) NOT NULL,
					`bar` VARCHAR(100) NULL,
					PRIMARY KEY (`id`)
				)');

		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'bar' => 'bar1'));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'bar' => 'bar2'));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo3', 'bar' => 'bar3'));
	}

	public static function tearDownAfterClass() {
		CM_Mysql::exec('DROP TABLE TBL_TEST');
	}

	public function testFetchAssoc() {
		$result = CM_Mysql::select(TBL_TEST, 'bar', array('foo' => 'foo1'));
		$this->assertEquals(array('bar' => 'bar1'), $result->fetchAssoc());
		
		$result = CM_Mysql::select(TBL_TEST, 'bar', array('foo' => 'nonexistent'));
		$this->assertEquals(false, $result->fetchAssoc());
	}

}
