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


}
