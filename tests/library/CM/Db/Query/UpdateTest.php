<?php

class CM_Db_Query_UpdateTest extends CMTest_TestCase {

    /** @var CM_Db_Client */
    private static $_client;

    public static function setUpBeforeClass() {
        self::$_client = CM_Db_Db::getClient();
    }

    public function testAll() {
        $query = new CM_Db_Query_Update(self::$_client, 't`est', array('f`oo' => 2, 'b`ar' => null), array('f`oo' => '1'));
        $this->assertSame('UPDATE `t``est` SET `f``oo` = ?, `b``ar` = NULL WHERE `f``oo` = ?', $query->getSqlTemplate());
        $this->assertEquals(array(2, '1'), $query->getParameters());
    }
}
