<?php

class CM_Clockwork_EventTest extends CMTest_TestCase {

    public function testShouldRunInterval() {
        $currently = new DateTime();
        $eventMock = $this->mockClass('CM_Clockwork_Event');
        $eventMock->mockMethod('_getCurrentDateTime')->set(function () use ($currently) {
            return clone($currently);
        });
        /** @var CM_Clockwork_Event $event */
        $event = $eventMock->newInstance(['event', '2 seconds']);
        $this->assertFalse($event->shouldRun());
        $currently->modify('2 seconds');
        $this->assertTrue($event->shouldRun());
        $lastRunTime = clone($currently);

        $this->assertFalse($event->shouldRun($lastRunTime));
        $currently->modify('1 second');
        $this->assertFalse($event->shouldRun($lastRunTime));
        $currently->modify('1 second');
        $this->assertTrue($event->shouldRun($lastRunTime));
 }

    public function testShouldRunFixedTime() {
        $currently = new DateTime();
        $eventMock = $this->mockClass('CM_Clockwork_Event');
        $eventMock->mockMethod('_getCurrentDateTime')->set(function () use ($currently) {
            return clone($currently);
        });
        /** @var CM_Clockwork_Event $event */
        $event = $eventMock->newInstance(['event1', '14:00']);
        $currently->modify('13:59:59');
        $this->assertFalse($event->shouldRun());

        $currently->modify('2 seconds');
        $this->assertTrue($event->shouldRun());

        $lastRunTime = clone($currently);
        $this->assertFalse($event->shouldRun($lastRunTime));

        $currently->modify('1 day');
        $this->assertTrue($event->shouldRun($lastRunTime));

        $event = $eventMock->newInstance(['event2', 'first day of 09:00']);
        $currently->modify('first day of 08:59');
        $this->assertFalse($event->shouldRun());

        $currently->modify('last day of');
        $this->assertTrue($event->shouldRun());

        $lastRunTime = clone($currently);
        $this->assertFalse($event->shouldRun($lastRunTime));

        $currently->modify('next day 09:00');
        $this->assertTrue($event->shouldRun($lastRunTime));
    }

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
