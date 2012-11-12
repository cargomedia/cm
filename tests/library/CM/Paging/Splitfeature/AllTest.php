<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_Splitfeature_AllTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testPaging() {
		CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		CM_Model_Splitfeature::create(array('name' => 'bar', 'percentage' => 10));
		CM_Model_Splitfeature::create(array('name' => 'foobar', 'percentage' => 30));
		CM_Model_Splitfeature::create(array('name' => 'foofoobar', 'percentage' => 88));

		$paging = new CM_Paging_Splitfeature_All();
		$this->assertSame(4, $paging->getCount());
	}
}