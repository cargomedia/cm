<?php

class CM_Clockwork_EventTest extends CMTest_TestCase {

    public function testShouldRun() {
        $currently = new DateTime();
        $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTime'))->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));
        $event->__construct('event', new DateInterval('PT2S'));
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

        $future = clone $currently;
        $future->add(new DateInterval('PT1S'));
        $this->assertFalse($event->shouldRun($future));

        $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($currently) {
            return clone $currently;
        }));

        $future = clone $currently;
        $future->add(new DateInterval('PT5S'));
        $event->__construct('event', new DateInterval('PT2S'), $future);

        $currently->add(new DateInterval('PT2S'));
        $this->assertFalse($event->shouldRun());

        $past = clone $currently;
        $past->sub(new DateInterval('PT2S'));

        $this->assertTrue($event->shouldRun($past));
    }

    public function testRun() {
        $counter = array(
            'foo' => 0,
            'bar' => 0,
        );
        $event = new CM_Clockwork_Event('event', new DateInterval('PT1S'));
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
