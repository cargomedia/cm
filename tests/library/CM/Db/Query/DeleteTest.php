<?php

class CM_Db_Query_DeleteTest extends CMTest_TestCase {

    /** @var CM_Db_Client */
    private static $_client;

    public static function setUpBeforeClass() {
        self::$_client = CM_Db_Db::getClient();
    }

    public function testDelete() {
        $query = new CM_Db_Query_Delete(self::$_client, 't`est', array('foo' => 'foo1'));
        $this->assertSame('DELETE FROM `t``est` WHERE `foo` = ?', $query->getSqlTemplate());
    }
}
