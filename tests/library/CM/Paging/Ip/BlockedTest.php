<?php
require_once __DIR__ . '/../../../../TestCase.php';

class CM_Paging_Ip_BlockedTest extends TestCase {
	
	public static function setUpBeforeClass() {	
	}
	
	public static function tearDownAfterClass() {
		
		CM_CacheLocal::flush();
	}
	
	public function testAdd() {
		$ip = '127.0.0.1';
		$ip2 = '127.0.0.2';
		$paging = new CM_Paging_Ip_Blocked();
		$paging->add(ip2long($ip));
		$this->assertEquals(1, $paging->getCount());
		$entry = $paging->getItem(0);
		$this->assertTrue($paging->contains(ip2long($ip)));
		TH::timeDaysForward(2);
		$paging->add(ip2long($ip2));
		CM_CacheLocal::flush();
		$paging->_change();
		$this->assertEquals(2, $paging->getCount());
		TH::timeDaysForward(2);
		CM_Paging_Ip_Blocked::deleteOlder(3 * 86400);
		CM_CacheLocal::flush();
		$paging->_change();
		$this->assertEquals(1, $paging->getCount());
		TH::timeDaysForward(2);
		CM_Paging_Ip_Blocked::deleteOlder(3 * 86400);
		CM_CacheLocal::flush();
		$this->assertEquals(1, $paging->getCount());
		$paging->_change();
		$this->assertEquals(0, $paging->getCount());
	}
}
