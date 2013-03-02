<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public function setUp() {
		CM_Mysql::exec('CREATE TABLE test (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`foo` VARCHAR(100) NOT NULL,
						`bar` VARCHAR(100) NULL,
						`sequence` INT UNSIGNED NOT NULL,
						PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}

	public function tearDown() {
		CM_Mysql::exec('DROP TABLE test');
	}

	public function testSelect() {
		CM_Db_Db::insert('test', array('foo' => 'foo1', 'bar' => 'bar1'));
		CM_Db_Db::insert('test', array('foo' => '2', 'bar' => 'bar2'));
		CM_Db_Db::insert('test', array('foo' => 'foo3', 'bar' => 'bar3'));

		$result = CM_Db_Db::select('test', 'bar', array('foo' => 'foo1'));
		$this->assertEquals('bar1', $result->fetchOne());

		$result = CM_Db_Db::select('test', array('foo', 'bar'), array('id' => 2, 'foo' => 2));
		$this->assertEquals(array('foo' => '2', 'bar' => 'bar2'), $result->fetchAssoc());

		$result = CM_Db_Db::select('test', array('foo', 'bar'), array('id' => 2, 'foo' => 3));
		$this->assertEquals(0, $result->numRows());

		$result = CM_Db_Db::select('test', 'foo', array('id' => 'nonexistent'));
		$this->assertEquals(0, $result->numRows());
	}

}
