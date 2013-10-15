<?php

class CM_Action_AbstractTest extends CMTest_TestCase {

	public function setUp() {
		CM_Config::get()->CM_Action_Abstract->verbs['Foo'] = 1;
	}

	public function testNotify() {
		$actor = CMTest_TH::createUser();
		$action = $this->getMockBuilder('CM_Action_Abstract')->setMethods(array('_notifyFoo', '_track'))
				->setConstructorArgs(array('Foo', $actor))->getMockForAbstractClass();
		// Cannot check due to https://github.com/sebastianbergmann/phpunit-mock-objects/issues/139
		// $action->expects($this->once())->method('_notifyFoo')->with('bar');
		$action->expects($this->once())->method('_track');

		$method = CMTest_TH::getProtectedMethod('CM_Action_Abstract', '_notify');
		$method->invoke($action, 'bar');
	}

	public function testTrack() {
		CM_Config::get()->CM_KissTracking->enabled = true;
		$tracking = CM_KissTracking::getInstance();
		$getEventsMethod = CMTest_TH::getProtectedMethod('CM_KissTracking', '_getEvents');

		$actor = CMTest_TH::createUser();
		$action = $this->getMockBuilder('CM_Action_Abstract')->setConstructorArgs(array('Foo', $actor))->getMockForAbstractClass();

		$getEventsMethod->invoke($tracking);
		$nofifyMethod = CMTest_TH::getProtectedMethod('CM_Action_Abstract', '_notify');
		$nofifyMethod->invoke($action, 'bar');
		$this->assertCount(1, $getEventsMethod->invoke($tracking));
	}
}
