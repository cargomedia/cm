<?php

class CM_Clockwork_Storage_MemoryTest extends CMTest_TestCase {

    public function testGetSetLastRuntime() {
        $storage = new CM_Clockwork_Storage_Memory();
        $event = new CM_Clockwork_Event('foo', '1 second');

        $this->assertNull($storage->getLastRuntime($event));

        $lastRuntime = new DateTime();
        $storage->setRuntime($event, $lastRuntime);
        $this->assertEquals($lastRuntime, $storage->getLastRuntime($event));

        $lastRuntime->modify('1 second');
        $this->assertEquals($lastRuntime, $storage->getLastRuntime($event)->modify('1 second'));
        $storage->setRuntime($event, $lastRuntime);
        $this->assertEquals($lastRuntime, $storage->getLastRuntime($event));
    }
}
