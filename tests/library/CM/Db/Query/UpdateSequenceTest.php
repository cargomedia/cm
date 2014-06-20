<?php

class CM_Db_Query_UpdateSequenceTest extends CMTest_TestCase {

    public function testAll() {
        $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $query = new CM_Db_Query_UpdateSequence($client, 't`est', 's`ort', -1, array('f`oo' => 'bar'), 4, 9);
        $this->assertSame('UPDATE `t``est` SET `s``ort` = `s``ort` + ? WHERE `f``oo` = ? AND `s``ort` BETWEEN ? AND ?', $query->getSqlTemplate());
        $this->assertEquals(array(-1, 'bar', 4, 9), $query->getParameters());
    }

    public function testWithoutWhere() {
        $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $query = new CM_Db_Query_UpdateSequence($client, 't`est', 's`ort', -1, null, 4, 9);
        $this->assertSame('UPDATE `t``est` SET `s``ort` = `s``ort` + ? WHERE `s``ort` BETWEEN ? AND ?', $query->getSqlTemplate());
        $this->assertEquals(array(-1, 4, 9), $query->getParameters());
    }
}
