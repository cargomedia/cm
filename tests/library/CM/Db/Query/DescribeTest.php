<?php

class CM_Db_Query_DescribeTest extends CMTest_TestCase {

    public function testWithColumn() {
        $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $query = new CM_Db_Query_Describe($client, 't`est table', 't`est column');
        $this->assertSame('DESCRIBE `t``est table` `t``est column`', $query->getSqlTemplate());
    }

    public function testWithoutColumn() {
        $client = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $query = new CM_Db_Query_Describe($client, 't`est table');
        $this->assertSame('DESCRIBE `t``est table`', $query->getSqlTemplate());
    }
}
