<?php

class CM_Paging_Log_AbstractTest extends CMTest_TestCase {

  public function tearDown() {
    CMTest_TH::clearEnv();
  }

  public function testAddGet() {
    $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')->setMethods(array('getType'))
      ->disableOriginalConstructor()->getMockForAbstractClass();
    $paging->expects($this->any())->method('getType')->will($this->returnValue(14));
    /** @var CM_Paging_Log_Abstract $paging */
    $paging->__construct();

    $add = CMTest_TH::getProtectedMethod('CM_Paging_Log_Abstract', '_add');
    $add->invoke($paging, 'foo');
    $add->invoke($paging, 'bar', array('meta1' => 12));

    $items = $paging->getItems();
    $this->assertSame(2, count($items));

    $this->assertSame('bar', $items[0]['msg']);
    $this->assertSame(array('meta1' => 12), $items[0]['metaInfo']);

    $this->assertSame('foo', $items[1]['msg']);
    $this->assertSame(null, $items[1]['metaInfo']);
  }

  public function testGetInvalidMetaInfo() {
    $paging = $this->getMockBuilder('CM_Paging_Log_Abstract')->setMethods(array('getType'))
      ->disableOriginalConstructor()->getMockForAbstractClass();
    $paging->expects($this->any())->method('getType')->will($this->returnValue(14));
    /** @var CM_Paging_Log_Abstract $paging */
    $paging->__construct();

    $add = CMTest_TH::getProtectedMethod('CM_Paging_Log_Abstract', '_add');
    $stringTooLongForDb = str_repeat('x', 99999);
    $add->invoke($paging, 'foo', array('meta1' => $stringTooLongForDb));

    $items = $paging->getItems();
    $this->assertSame(1, count($items));

    $this->assertSame('foo', $items[0]['msg']);
    $this->assertSame(null, $items[0]['metaInfo']);
  }
}
