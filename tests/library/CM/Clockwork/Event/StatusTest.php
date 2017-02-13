<?php

class CM_Clockwork_Event_StatusTest extends CMTest_TestCase {

    public function testSetLastRuntime() {
        $status = new CM_Clockwork_Event_Status();
        $this->assertSame(null, $status->getLastRuntime());

        $lastRuntime = new DateTime('now');
        $status->setLastRuntime($lastRuntime);
        $this->assertSame($lastRuntime, $status->getLastRuntime());

        $status->setLastRuntime(null);
        $this->assertSame(null, $status->getLastRuntime());
    }

    public function testLastStartTime() {
        $status = new CM_Clockwork_Event_Status();
        $this->assertSame(null, $status->getLastStartTime());

        $lastStartTime = new DateTime('now');
        $status->setLastStartTime($lastStartTime);
        $this->assertSame($lastStartTime, $status->getLastStartTime());

        $status->setLastStartTime(null);
        $this->assertSame(null, $status->getLastStartTime());
    }

    public function testSetRunning() {
        $status = new CM_Clockwork_Event_Status();
        $this->assertSame(false, $status->isRunning());

        $status->setRunning(true);
        $this->assertSame(true, $status->isRunning());

        $status->setRunning(false);
        $this->assertSame(false, $status->isRunning());
    }
}
