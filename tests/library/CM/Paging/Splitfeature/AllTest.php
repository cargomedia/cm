<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_Splitfeature_AllTest extends TestCase {

	public function setUp() {
		CM_Config::get()->CM_Model_Splitfeature->withoutPersistence = false;
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		CM_Model_Splitfeature::create(array('name' => 'bar', 'percentage' => 10));
		$paging = new CM_Paging_Splitfeature_All();
		$this->assertInstanceOf('CM_Model_Splitfeature', $paging->getItem(0));
		$this->assertSame(2, count($paging->getItems()));

		CM_Model_Splitfeature::create(array('name' => 'foobar', 'percentage' => 30));
		$splitfeature = CM_Model_Splitfeature::create(array('name' => 'foofoobar', 'percentage' => 88));
		$paging = new CM_Paging_Splitfeature_All();
		$this->assertSame(4, count($paging->getItems()));

		$splitfeature->delete();
		$paging = new CM_Paging_Splitfeature_All();
		$this->assertSame(3, count($paging->getItems()));
	}
}
