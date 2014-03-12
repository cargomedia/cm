<?php

class CM_Paging_Splitfeature_AllTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Model_Splitfeature->withoutPersistence = false;
    }

    public function testPaging() {
        CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        CM_Model_Splitfeature::createStatic(array('name' => 'bar', 'percentage' => 10));
        $paging = new CM_Paging_Splitfeature_All();
        $this->assertInstanceOf('CM_Model_Splitfeature', $paging->getItem(0));
        $this->assertSame(2, count($paging->getItems()));

        CM_Cache_Local::getInstance()->flush();
        CM_Model_Splitfeature::createStatic(array('name' => 'foobar', 'percentage' => 30));
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foofoobar', 'percentage' => 88));
        $paging = new CM_Paging_Splitfeature_All();
        $this->assertSame(4, count($paging->getItems()));

        $splitfeature->delete();
        CM_Cache_Local::getInstance()->flush();
        $paging = new CM_Paging_Splitfeature_All();
        $this->assertSame(3, count($paging->getItems()));
    }
}
