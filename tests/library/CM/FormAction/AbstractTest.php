<?php

class CM_FormAction_AbstractTest extends CMTest_TestCase {

  public function testConstruct() {
    $mockForm = $this->getMockForm();
    /** @var CM_FormAction_Abstract $mockFormAction */
    $mockFormAction = $this->getMockForAbstractClass('CM_FormAction_Abstract', array($mockForm), 'CM_FormAction_Foo_Bar_BlaBlaBla');
    $this->assertSame('Bar_BlaBlaBla', $mockFormAction->getName());
  }
}
