<?php

class CM_Paging_Log_AbstractTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testAddGet() {
        $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')->setMethods(array('getType'))->disableOriginalConstructor()->getMockForAbstractClass();
        $paging->expects($this->any())->method('getType')->will($this->returnValue(14));
        /** @var CM_Paging_Log_Abstract $paging */
        $paging->__construct();

        $add = CMTest_TH::getProtectedMethod('CM_Paging_Log_Abstract', '_add');
        $add->invoke($paging, 'foo');
        $obj = CMTest_TH::createUser();
        $add->invoke($paging, 'bar', array('meta1' => 12, 'meta2' => $obj));

        $items = $paging->getItems();
        $this->assertSame(2, count($items));

        $this->assertSame('bar', $items[0]['msg']);
        $this->assertSame(array('meta1' => 12, 'meta2' => CM_Util::varDump($obj)), $items[0]['metaInfo']);

        $this->assertSame('foo', $items[1]['msg']);
        $this->assertSame(null, $items[1]['metaInfo']);
    }

    public function testAddEmptyMetaInfo() {
        $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')->setMethods(array('getType'))->disableOriginalConstructor()->getMockForAbstractClass();
        $paging->expects($this->any())->method('getType')->will($this->returnValue(14));
        /** @var CM_Paging_Log_Abstract $paging */
        $paging->__construct();

        $add = CMTest_TH::getProtectedMethod('CM_Paging_Log_Abstract', '_add');
        $add->invoke($paging, 'foo');
        $add->invoke($paging, 'bar', []);

        $this->assertSame(null, $paging->getItem(0)['metaInfo']);
        $this->assertSame(null, $paging->getItem(1)['metaInfo']);
    }

    public function testGetInvalidMetaInfo() {
        $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')->setMethods(array('getType'))
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $paging->expects($this->any())->method('getType')->will($this->returnValue(14));
        /** @var CM_Paging_Log_Abstract $paging */
        $paging->__construct();

        CM_Db_Db::insert('cm_log', array('msg'       => 'foo', 'metaInfo' => str_ireplace('{', '/', serialize(array('foo' => 'bar'))),
                                         'timeStamp' => time(), 'type' => 14));

        $items = $paging->getItems();
        $this->assertSame(1, count($items));

        $this->assertSame('foo', $items[0]['msg']);
        $this->assertSame(null, $items[0]['metaInfo']);
    }

    public function testCleanUp() {
        $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMockForAbstractClass();
        $paging->expects($this->any())->method('getType')->will($this->returnValue(1));
        /** @var CM_Paging_Log_Abstract $paging */
        $paging->__construct();

        $this->assertSame(0, $paging->getCount());

        CMTest_TH::getProtectedMethod($paging, '_add')->invoke($paging, 'foo');
        $paging->_change();
        $this->assertSame(1, $paging->getCount());

        $age = 7 * 86400;
        CMTest_TH::timeForward($age);
        $paging->cleanUp();
        $this->assertSame(0, $paging->getCount());
    }

    public function testAggregate() {
        $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMockForAbstractClass();
        $paging->expects($this->any())->method('getType')->will($this->returnValue(1));
        /** @var CM_Paging_Log_Abstract $paging */
        $paging->__construct();

        CMTest_TH::callProtectedMethod($paging, '_add', ['haha']);
        CMTest_TH::callProtectedMethod($paging, '_add', ['huhu']);
        CMTest_TH::timeDaysForward(1);
        CMTest_TH::timeDaysForward(1);
        CMTest_TH::callProtectedMethod($paging, '_add', ['haha']);
        CMTest_TH::callProtectedMethod($paging, '_add', ['haha', array('id' => 123123, 'ip' => 1234123)]);
        CMTest_TH::timeDaysForward(1);
        CMTest_TH::callProtectedMethod($paging, '_add', ['ha']);
        CMTest_TH::callProtectedMethod($paging, '_add', ['ha']);
        CMTest_TH::callProtectedMethod($paging, '_add', ['ha']);

        $paging->__construct(true, 2 * 86400);
        $this->assertEquals(2, $paging->getCount());
        $warning1 = $paging->getItem(0);
        $warning2 = $paging->getItem(1);
        $this->assertEquals(3, $warning1['count']);
        $this->assertEquals(2, $warning2['count']);
        $this->assertEquals('haha', $warning2['msg']);
        $this->assertEquals('ha', $warning1['msg']);
    }
}
