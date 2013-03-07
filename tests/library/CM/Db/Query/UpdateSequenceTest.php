<?php

class CM_Db_Query_UpdateSequenceTest extends CMTest_TestCase {

	public function testAll() {
		$query = new CM_Db_Query_UpdateSequence('t`est', 's`ort', '-1', array('f`oo' => 'bar'), 4, 9);
		$this->assertSame('UPDATE `t``est` SET `s``ort` = `s``ort` + ? WHERE `f``oo` = ? AND `?` BETWEEN ? AND ?', $query->getSqlTemplate());
		$this->assertEquals(array('-1', 'bar', 's`ort', 4, 9), $query->getParameters());
	}
}
