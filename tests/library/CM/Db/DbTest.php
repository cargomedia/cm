<?php

class CM_Db_DbTest extends CMTest_TestCase {

	public function setUp() {
		CM_Db_Db::exec('
			CREATE TABLE `test` (
				`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
				`foo` VARCHAR(100) NULL,
				`bar` VARCHAR(100) NULL,
				`sequence` INT UNSIGNED NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY (`foo`)
			)');
	}

	public function tearDown() {
		CM_Db_Db::exec('DROP TABLE `test`');
	}

	public function testCount() {
		$this->assertSame(0, CM_Db_Db::count('test'));
		CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		$this->assertSame(1, CM_Db_Db::count('test'));
	}

	public function testDeleteSequence() {
		$id1 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		$id2 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 2));
		$id3 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 3));
		$id4 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 4));
		CM_Db_Db::deleteSequence('test', 'sequence', array('id' => $id2));
		$this->assertRow('test', array('id' => $id1, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id3, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id4, 'sequence' => 3));
	}

	public function testDeleteSequenceWithWhere() {
		$id1 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		$id2 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 2));
		$id3 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 1));
		$id4 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 2));
		$id5 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 3));
		CM_Db_Db::deleteSequence('test', 'sequence', array('id' => $id4), array('bar' => 'bar2'));
		$this->assertRow('test', array('id' => $id1, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id2, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id3, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id5, 'sequence' => 2));
	}

	public function testDescribeColumn() {
		CM_Db_Db::exec('
			CREATE TABLE `test2` (
				`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			)');
		$column = CM_Db_Db::describeColumn('test2', 'id');
		$this->assertSame('id', $column->getName());
		$this->assertSame('int', $column->getType());
		$this->assertSame(12, $column->getSize());
		$this->assertNull($column->getEnum());
		$this->assertTrue($column->getUnsigned());
		$this->assertFalse($column->getAllowNull());
		$this->assertNull($column->getDefaultValue());
		CM_Db_Db::exec('DROP TABLE `test2`');
	}

	public function testDescribeColumnThrowsException() {
		CM_Db_Db::exec('
			CREATE TABLE `test2` (
				`id` int(12) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`id`)
			)');
		try {
			CM_Db_Db::describeColumn('test2', 'id');
			CM_Db_Db::describeColumn('test2', 'id1');
			$this->fail('Column doesn\'t exist');
		} catch (CM_Db_Exception $e) {
			$this->assertContains('`id1`', $e->getMessage());
		}

		try {
			CM_Db_Db::describeColumn('test_', 'id');
			$this->fail('Table doesn\'t exist');
		} catch (CM_Db_Exception $e) {
			$this->assertContains('`id`', $e->getMessage());
		}
		CM_Db_Db::exec('DROP TABLE `test2`');
	}

	public function testExistsColumn() {
		$this->assertSame(true, CM_Db_Db::existsColumn('test', 'foo'));
		$this->assertSame(false, CM_Db_Db::existsColumn('test', 'test'));
	}

	public function testExistsIndex() {
		$this->assertSame(true, CM_Db_Db::existsIndex('test', 'foo'));
		$this->assertSame(false, CM_Db_Db::existsIndex('test', 'test'));
	}

	public function testExistsTable() {
		$this->assertSame(true, CM_Db_Db::existsTable('test'));
		$this->assertSame(false, CM_Db_Db::existsTable('foo'));
	}

	public function testUpdateSequence() {
		$id1 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		$id2 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 2));
		$id3 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 3));
		$id4 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 1));
		$id5 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 2));
		$id6 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 3));
		CM_Db_Db::updateSequence('test', array('sequence' => 2), array('id' => $id1), array('bar' => 'bar1'));
		$this->assertRow('test', array('id' => $id1, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id2, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id3, 'sequence' => 3));
		$this->assertRow('test', array('id' => $id4, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id5, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id6, 'sequence' => 3));
		CM_Db_Db::updateSequence('test', array('sequence' => 1), array('id' => $id3), array('bar' => 'bar1'));
		$this->assertRow('test', array('id' => $id1, 'sequence' => 3));
		$this->assertRow('test', array('id' => $id2, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id3, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id4, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id5, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id6, 'sequence' => 3));
	}

	public function testUpdateSequenceWithoutWhere() {
		$id1 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		$id2 = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 2));
		$id3 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 3));
		$id4 = CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 4));
		CM_Db_Db::updateSequence('test', array('sequence' => 4), array('id' => $id1));
		$this->assertRow('test', array('id' => $id1, 'sequence' => 4));
		$this->assertRow('test', array('id' => $id2, 'sequence' => 1));
		$this->assertRow('test', array('id' => $id3, 'sequence' => 2));
		$this->assertRow('test', array('id' => $id4, 'sequence' => 3));
	}

	public function testUpdateSequenceOutOfBounds() {
		$id = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		try {
			CM_Db_Db::updateSequence('test', array('sequence' => 2), array('id' => $id), array('bar' => 'bar1'));
			$this->fail('Sequence not out of bounds.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Sequence out of bounds.', $ex->getMessage());
		}
		try {
			CM_Db_Db::updateSequence('test', array('sequence' => 0), array('id' => $id), array('bar' => 'bar1'));
			$this->fail('Sequence not out of bounds.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Sequence out of bounds.', $ex->getMessage());
		}
	}

	public function testUpdateSequenceInvalidWhere() {
		$id = CM_Db_Db::insert('test', array('bar' => 'bar1', 'sequence' => 1));
		CM_Db_Db::insert('test', array('bar' => 'bar2', 'sequence' => 1));
		try {
			CM_Db_Db::updateSequence('test', array('sequence' => 1), array('id' => $id), array('bar' => 'bar2'));
			$this->fail('Able to retrieve original sequence number.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not retrieve original sequence number.', $ex->getMessage());
		}
		try {
			CM_Db_Db::updateSequence('test', array('sequence' => 1), array('id' => 2), array('bar' => 'bar1'));
			$this->fail('Able to retrieve original sequence number.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not retrieve original sequence number.', $ex->getMessage());
		}
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
