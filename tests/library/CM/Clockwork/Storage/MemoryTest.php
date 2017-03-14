<?php

class CM_Clockwork_Storage_MemoryTest extends CMTest_TestCase {

    public function testGetSetStatus() {
        $storage = new CM_Clockwork_Storage_Memory();
        $event = new CM_Clockwork_Event('foo', '1 second');

        $this->assertEquals(new CM_Clockwork_Event_Status(), $storage->getStatus($event));
        $runTime = new DateTime('1 second ago');
        $startTime = new DateTime('1 second ago');
        $status = (new CM_Clockwork_Event_Status())->setLastRuntime($runTime)->setLastStartTime($startTime)->setRunning(true);
        $storage->setStatus($event, $status);
        $statusLoaded = $storage->getStatus($event);
        $this->assertEquals($status, $statusLoaded);

        $this->assertNotSame($startTime, $statusLoaded->getLastStartTime());
        $this->assertNotSame($runTime, $statusLoaded->getLastRuntime());
    }
}
