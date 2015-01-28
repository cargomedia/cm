<?php

class CM_Clockwork_EventTest extends CMTest_TestCase {

    public function testRun() {
        $counter = array(
            'foo' => 0,
            'bar' => 0,
        );
        $event = new CM_Clockwork_Event('event', '1 second');
        $event->registerCallback(function () use (&$counter) {
            $counter['foo']++;
        });
        $event->run();
        $event->registerCallback(function () use (&$counter) {
            $counter['bar']++;
        });
        $event->run();
        $this->assertSame(array(
            'foo' => 2,
            'bar' => 1,
        ), $counter);
    }
}
