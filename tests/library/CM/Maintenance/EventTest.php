<?php

class CM_Maintenance_EventTest extends CMTest_TestCase {

    public function testConstructor() {
        $event = new CM_Maintenance_Event('foo', '1 second', function() {

        });
        $this->assertSame('foo', $event->getName());
        $this->assertSame('1 second', $event->getDateTimeString());
        $this->assertEquals(new CM_Clockwork_Event('foo', '1 second'), $event->getClockworkEvent());
    }

    public function testRunCallback() {
        $counter = 0;
        $dateTimePassed = null;
        $event = new CM_Maintenance_Event('foo', '1 second', function(DateTime $lastRuntime = null) use (&$counter, &$dateTimePassed) {
            $counter++;
            $dateTimePassed = $lastRuntime;
        });
        $lastRuntime = new DateTime();
        $event->runCallback($lastRuntime);
        $this->assertSame(1, $counter);
        $this->assertSame($lastRuntime, $dateTimePassed);

        $event->runCallback(null);
        $this->assertSame(2, $counter);
        $this->assertSame(null, $dateTimePassed);
    }
}
