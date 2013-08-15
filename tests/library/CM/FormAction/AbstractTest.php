<?php

class CM_FormAction_AbstractTest extends CMTest_TestCase {

	public function testConstruct() {
		/** @var CM_FormAction_Abstract $mockFormAction */
		$mockFormAction = $this->getMockForAbstractClass('CM_FormAction_Abstract', array(), 'CM_FormAction_Foo_Bar_BlaBlaBla');
		$this->assertSame('bla_bla_bla', $mockFormAction->getName());
	}
}
