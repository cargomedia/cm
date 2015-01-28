<?php

class CM_Clockwork_Storage_AbstractTest extends CMTest_TestCase {

    public function testSetRuntime() {
        $storage = $this->mockObject('CM_Clockwork_Storage_Abstract', ['foo']);
        $event1 = new CM_Clockwork_Event('event1', '1 second');
        $event2 = new CM_Clockwork_Event('event2', '1 second');
        $executionTime1 = new DateTime();
        $executionTime2 = new DateTime('1 minute');
        $storage->mockMethod('_save')
            ->at(0, function ($data) use ($event1, $executionTime1) {
                $this->assertEquals([$event1->getName() => $executionTime1], $data);
            })
            ->at(1, function ($data) use ($event1, $event2, $executionTime1, $executionTime2) {
                $this->assertEquals([$event1->getName() => $executionTime1, $event2->getName() => $executionTime2], $data);
            })
            ->set(function () {
                throw new CM_Exception_Invalid('`_save()` called too often');
            });
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $storage->setRuntime($event1, $executionTime1);
        $storage->setRuntime($event2, $executionTime2);
    }

    public function testGetLastRuntime() {
        $storage = $this->mockObject('CM_Clockwork_Storage_Abstract', ['foo']);
        $event1 = new CM_Clockwork_Event('event1', '1 second');
        $event2 = new CM_Clockwork_Event('event2', '1 second');
        $executionTime1 = new DateTime();
        $executionTime2 = new DateTime('1 minute');
        $storage->mockMethod('_load')
            ->at(0, function () use ($event1, $event2, $executionTime1, $executionTime2) {
                return [$event1->getName() => $executionTime1, $event2->getName() => $executionTime2];
            })->set(function () {
                throw new CM_Exception_AuthRequired('`_load()` called too often');
            });
        /** @var CM_Clockwork_Storage_Abstract $storage */
        $this->assertEquals($executionTime1, $storage->getLastRuntime($event1));
        $this->assertNotSame($executionTime1, $storage->getLastRuntime($event1)); // make sure datetime object is cloned
        $this->assertEquals($executionTime2, $storage->getLastRuntime($event2));
    }
}
