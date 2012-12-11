<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_MysqlTest extends TestCase {

	public static function setUpBeforeClass() {
		define('TBL_TEST', 'test');
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function setUp() {
		CM_Mysql::exec('CREATE TABLE TBL_TEST (
						`id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
						`foo` VARCHAR(100) NOT NULL,
						`bar` VARCHAR(100) NULL,
						`sequence` INT UNSIGNED NOT NULL,
						PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}

	public function tearDown() {
		CM_Mysql::exec('DROP TABLE TBL_TEST');
	}

	public function testSelect() {
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'bar' => 'bar1'));
		CM_Mysql::insert(TBL_TEST, array('foo' => '2', 'bar' => 'bar2'));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo3', 'bar' => 'bar3'));

		$result = CM_Mysql::select(TBL_TEST, 'bar', array('foo' => 'foo1'));
		$this->assertEquals('bar1', $result->fetchOne());

		$result = CM_Mysql::select(TBL_TEST, array('foo', 'bar'), array('id' => 2, 'foo' => 2));
		$this->assertEquals(array('foo' => '2', 'bar' => 'bar2'), $result->fetchAssoc());
		
		$result = CM_Mysql::select(TBL_TEST, array('foo', 'bar'), array('id' => 2, 'foo' => 3));
		$this->assertEquals(0, $result->numRows());
		
		$result = CM_Mysql::select(TBL_TEST, 'foo', array('id' => 'nonexistent'));
		$this->assertEquals(0, $result->numRows());
	}

	public function testInsert() {
		// No values
		$id = CM_Mysql::insert(TBL_TEST, array());
		$this->assertRow(TBL_TEST, array('id' => $id));
		CM_Mysql::delete(TBL_TEST, array('id' => $id));
		
		// Null value insertion
		CM_Mysql::insert(TBL_TEST, array('bar' => null));
		$this->assertRow(TBL_TEST, array('bar' => null));
		
		// Associative array
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo1'));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'bar' => 'bar2'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo2', 'bar' => 'bar2'));

		// Scalar and array
		CM_Mysql::insert(TBL_TEST, 'foo', 'foo3');
		$this->assertRow(TBL_TEST, array('foo' => 'foo3'));
		CM_Mysql::insert(TBL_TEST, 'foo', array('foo4', 'foo5'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo4'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo5'));

		// Array and array
		CM_Mysql::insert(TBL_TEST, array('foo'), array('foo6'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo6'));
		CM_Mysql::insert(TBL_TEST, array('foo'), array('foo7', 'foo8'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo7'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo8'));
		CM_Mysql::insert(TBL_TEST, array('foo', 'bar'), array('foo9', 'bar9'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo9', 'bar' => 'bar9'));

		// Scalar and array of array
		CM_Mysql::insert(TBL_TEST, 'foo', array(array('foo10')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo10'));
		CM_Mysql::insert(TBL_TEST, 'foo', array(array('foo11'), array('foo12')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo11'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo12'));

		// Array and array of array
		CM_Mysql::insert(TBL_TEST, array('foo'), array(array('foo13')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo13'));
		CM_Mysql::insert(TBL_TEST, array('foo'), array(array('foo14'), array('foo15')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo14'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo15'));
		CM_Mysql::insert(TBL_TEST, array('foo', 'bar'), array(array('foo16', 'bar16')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo16', 'bar' => 'bar16'));
		CM_Mysql::insert(TBL_TEST, array('foo', 'bar'), array(array('foo17', 'bar17'), array('foo18', 'bar18')));
		$this->assertRow(TBL_TEST, array('foo' => 'foo17', 'bar' => 'bar17'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo18', 'bar' => 'bar18'));

		// Return value
		$insertId = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo19'));
		$this->assertInternalType('int', $insertId);
		$this->assertRow(TBL_TEST, array('id' => $insertId, 'foo' => 'foo19'));
		
		// IGNORE
		$insertId = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo20'));
		$rowCount = CM_Mysql::count(TBL_TEST);
		CM_Mysql::insertIgnore(TBL_TEST, array('id' => $insertId, 'foo' => 'foo21'));
		$this->assertSame($rowCount, CM_Mysql::count(TBL_TEST));
		
		// DELAYED
		$insertId = CM_Mysql::insertDelayed(TBL_TEST, array('foo' => 'foo22'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo22'));
		
		// ON DUPLICATE KEY UPDATE
		$insertId = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo23', 'bar' => 5));
		$this->assertRow(TBL_TEST, array('foo' => 'foo23', 'bar' => 5));
		
		CM_Mysql::insert(TBL_TEST, array('id' => $insertId, 'foo' => 'foo24'), null, array('foo' => 'foo25', 'bar' => 'bar25'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo25', 'bar' => 'bar25'));

		// Constraint violation
		$id = CM_Mysql::insert(TBL_TEST, array());
		try {
			CM_Mysql::insert(TBL_TEST, array('id' => $id));
			$this->fail('Can insert duplicate id');
		} catch(CM_Exception $e) {
			$this->assertContains('Duplicate entry', $e->getMessage());
		}
	}

	public function testUpdate() {
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'bar' => 'bar1'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo2'), array('foo' => 'foo1'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo2'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo3'), "`foo`='foo2'");
		$this->assertRow(TBL_TEST, array('foo' => 'foo3'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo4'), array('foo' => 'foo3', 'bar' => 'bar1'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo4'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo5'), array('foo' => 'nonexistent'));
		$this->assertNotRow(TBL_TEST, array('foo' => 'foo5'));

		CM_Mysql::update(TBL_TEST, array(), array('foo' => 'foo1'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo4'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo6'), array());
		$this->assertRow(TBL_TEST, array('foo' => 'foo6'));

		CM_Mysql::update(TBL_TEST, array('foo' => 'foo7', 'bar' => 'bar7'), array());
		$this->assertRow(TBL_TEST, array('foo' => 'foo7', 'bar' => 'bar7'));
		
		CM_Mysql::update(TBL_TEST, array('bar' => null), array());
		$this->assertRow(TBL_TEST, array('bar' => null));
		
		CM_Mysql::update(TBL_TEST, array('bar' => ''), array());
		$this->assertRow(TBL_TEST, array('bar' => ''));
		
		CM_Mysql::update(TBL_TEST, array('bar' => 0), array());
		$this->assertRow(TBL_TEST, array('bar' => '0'));

		// Return value
		$affectedRows = CM_Mysql::update(TBL_TEST, array('foo' => 'foo8'), array());
		$this->assertInternalType('int', $affectedRows);
		$this->assertEquals(1, $affectedRows);
	}

	public function testDelete() {
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1'));
		CM_Mysql::delete(TBL_TEST, array('foo' => 'foo1'));
		$this->assertNotRow(TBL_TEST, array('foo' => 'foo1'));

		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1'));
		CM_Mysql::delete(TBL_TEST, "`foo`='foo1'");
		$this->assertNotRow(TBL_TEST, array('foo' => 'foo1'));

		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo3'));
		CM_Mysql::delete(TBL_TEST, array('foo' => 'nonexistent'));
		$this->assertRow(TBL_TEST, array('foo' => 'foo3'));

		// Return value
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo4'));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo4'));
		$affectedRows = CM_Mysql::delete(TBL_TEST, array('foo' => 'foo4'));
		$this->assertInternalType('int', $affectedRows);
		$this->assertEquals(2, $affectedRows);
	}

	public function testUpdateSequence() {
		$id1 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 1));
		$id2 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 2));
		$id3 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 3));
		$id4 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 1));
		$id5 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 2));
		$id6 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 3));
		CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), 2, array('id' => $id1));
		$this->assertRow(TBL_TEST, array('id' => $id1, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id2, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id3, 'sequence' => 3));
		$this->assertRow(TBL_TEST, array('id' => $id4, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id5, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id6, 'sequence' => 3));
		CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), 1, array('id' => $id3));
		$this->assertRow(TBL_TEST, array('id' => $id1, 'sequence' => 3));
		$this->assertRow(TBL_TEST, array('id' => $id2, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id3, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id4, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id5, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id6, 'sequence' => 3));

	}

	public function testUpdateSequenceOutOfBounds() {
		$id = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 1));
		try {
			CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), 2, array('id' => $id));
			$this->fail('Sequence not out of bounds.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Sequence out of bounds.', $ex->getMessage());
		}
		try {
			CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), 0, array('id' => $id));
			$this->fail('Sequence not out of bounds.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Sequence out of bounds.', $ex->getMessage());
		}
	}

	public function testUpdateSequenceInvalidWhere() {
		$id = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 1));
		CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 1));
		try {
			CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo2'), 1, array('id' => $id));
			$this->fail('Able to retrieve original sequence number.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not retrieve original sequence number.', $ex->getMessage());
		}
		try {
			CM_Mysql::updateSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), 1, array('id' => 2));
			$this->fail('Able to retrieve original sequence number.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not retrieve original sequence number.', $ex->getMessage());
		}
	}

	public function testDeleteSequence() {
		CM_Mysql::deleteSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), array('id' => 1));
		$id1 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 1));
		$id2 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 2));
		$id3 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo1', 'sequence' => 3));
		$id4 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 1));
		$id5 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 2));
		$id6 = CM_Mysql::insert(TBL_TEST, array('foo' => 'foo2', 'sequence' => 3));
		CM_Mysql::deleteSequence(TBL_TEST, 'sequence', array('foo' => 'foo1'), array('id' => $id1));
		$this->assertRow(TBL_TEST, array('id' => $id2, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id3, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id4, 'sequence' => 1));
		$this->assertRow(TBL_TEST, array('id' => $id5, 'sequence' => 2));
		$this->assertRow(TBL_TEST, array('id' => $id6, 'sequence' => 3));
		$this->assertNotRow(TBL_TEST, array('id' => $id1));
	}
}
