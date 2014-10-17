<?php

class CM_Clockwork_EventTest extends CMTest_TestCase {

    public function testShouldRun() {
        $currently = new DateTime();
        $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTime'))->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));
        $event->__construct('event', '2 seconds');
        /** @var CM_Clockwork_Event $event */
        $this->assertFalse($event->shouldRun());
        $currently->add(new DateInterval('PT2S'));
        $this->assertTrue($event->shouldRun());
        $event->run();
        $this->assertFalse($event->shouldRun());
        $currently->add(new DateInterval('PT1S'));
        $this->assertFalse($event->shouldRun());
        $currently->add(new DateInterval('PT1S'));
        $this->assertTrue($event->shouldRun());
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
