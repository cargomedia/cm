<?php

class CM_Paging_Ip_BlockedTest extends CMTest_TestCase {

    public function testAdd() {
        CM_Config::get()->CM_Paging_Ip_Blocked->maxAge = (3 * 86400);
        $ip = '127.0.0.1';
        $ip2 = '127.0.0.2';

        $paging = new CM_Paging_Ip_Blocked();
        $paging->add(ip2long($ip));
        $this->assertEquals(1, $paging->getCount());
        $paging->getItem(0);
        $this->assertTrue($paging->contains(ip2long($ip)));

        CMTest_TH::timeDaysForward(2);
        $paging->add(ip2long($ip2));
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(2, $paging->getCount());

        CMTest_TH::timeDaysForward(2);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(1, $paging->getCount());

        CMTest_TH::timeDaysForward(2);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $this->assertEquals(1, $paging->getCount());
        $paging->_change();
        $this->assertEquals(0, $paging->getCount());

        $paging->add(ip2long($ip));
        $paging->add(ip2long($ip));
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(1, $paging->getCount());
        $this->assertTrue($paging->contains(ip2long($ip)));

        CMTest_TH::timeDaysForward(4);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(1, $paging->getCount());

        CMTest_TH::timeDaysForward(4);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $this->assertEquals(1, $paging->getCount());
        $paging->_change();
        $this->assertEquals(0, $paging->getCount());

        $paging->add(ip2long($ip));
        CMTest_TH::timeDaysForward(5);
        $paging->add(ip2long($ip));
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(1, $paging->getCount());
        $this->assertTrue($paging->contains(ip2long($ip)));

        CMTest_TH::timeDaysForward(2);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $paging->_change();
        $this->assertEquals(1, $paging->getCount());

        CMTest_TH::timeDaysForward(2);
        CM_Paging_Ip_Blocked::deleteOld();
        CM_Cache_Local::getInstance()->flush();
        $this->assertEquals(1, $paging->getCount());
        $paging->_change();
        $this->assertEquals(0, $paging->getCount());
    }
}
