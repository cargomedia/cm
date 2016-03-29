<?php

class CM_Class_TypeDumperTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoad() {
        CM_Config::get()->Foo = new stdClass();
        CM_Config::get()->Foo->types = [
            1111 => 'Foo1',
            2222 => 'Foo2',
        ];
        CM_Config::get()->Bar = new stdClass();
        CM_Config::get()->Bar->types = [
            3333 => 'Bar1',
            4444 => 'Bar2',
        ];

        $dumper = new CM_Class_TypeDumper($this->getServiceManager());
        $dumper->load(new CM_OutputStream_Null());

        $rowList = [
            ['type' => 1111, 'className' => 'Foo1'],
            ['type' => 2222, 'className' => 'Foo2'],
            ['type' => 3333, 'className' => 'Bar1'],
            ['type' => 4444, 'className' => 'Bar2'],
        ];
        $dbClient = $this->getServiceManager()->getDatabases()->getMaster();
        foreach ($rowList as $row) {
            $queryCount = new CM_Db_Query_Count($dbClient, 'cm_tmp_classType', $row);
            $this->assertSame('1', $queryCount->execute()->fetchColumn());
        }
    }

    public function testLoadTwice() {
        $dumper = new CM_Class_TypeDumper($this->getServiceManager());

        CM_Config::get()->Foo = new stdClass();
        CM_Config::get()->Foo->types = [
            1111 => 'Foo1',
            2222 => 'Foo2',
            3333 => 'Foo3',
        ];
        $dumper->load(new CM_OutputStream_Null());

        CM_Config::get()->Foo->types = [
            1111 => 'Foo1',
            2222 => 'Foo2',
        ];
        $dumper->load(new CM_OutputStream_Null());

        $dbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $this->assertSame('1', (new CM_Db_Query_Count($dbClient, 'cm_tmp_classType', ['type' => 1111]))->execute()->fetchColumn());
        $this->assertSame('1', (new CM_Db_Query_Count($dbClient, 'cm_tmp_classType', ['type' => 2222]))->execute()->fetchColumn());
        $this->assertSame('0', (new CM_Db_Query_Count($dbClient, 'cm_tmp_classType', ['type' => 3333]))->execute()->fetchColumn());
    }
}
