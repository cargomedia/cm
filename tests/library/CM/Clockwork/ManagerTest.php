<?php

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    public function testRunEventsFor() {
        $manager = new CM_Clockwork_Manager();
        $counter = array(
            '1'  => 0,
            '2'  => 0,
            '5'  => 0,
            '60' => 0,
        );
        $currently = new DateTime();
        $this->_createEvent($manager, $currently, new DateInterval('PT1S'), $counter, 'event1');
        $this->_createEvent($manager, $currently, new DateInterval('PT2S'), $counter, 'event2');
        $this->_createEvent($manager, $currently, new DateInterval('PT5S'), $counter, 'event3');
        $this->_createEvent($manager, $currently, new DateInterval('PT60S'), $counter, 'event4');

        for ($i = 0; $i < 100; $i++) {
            $currently->add(new DateInterval('PT1S'));
            $manager->runEvents();
        }
        $this->assertSame(array(
            '1'  => 100,
            '2'  => 50,
            '5'  => 20,
            '60' => 2,
        ), $counter);
    }

    public function testRunEventsPersistence() {
        $currently = new DateTime();
        var_dump($this->_getCurrentDateTime());
        $adapter = $this->getMockBuilder('CM_Clockwork_PersistenceAdapter_Abstract')->disableOriginalConstructor()->setMethods(array('load', 'save'))
            ->getMockForAbstractClass();
        $adapter->expects($this->any())->method('load')->will($this->returnValue(array('event2' => $this->_getCurrentDateTime())));
        $adapter->expects($this->at(1))->method('save')->with(array('event2' => $this->_getCurrentDateTime(), 'event1' => $this->_getCurrentDateTime(1)));
        $adapter->expects($this->at(2))->method('save')->with(array('event2' => $this->_getCurrentDateTime(), 'event1' => $this->_getCurrentDateTime(2)));
        $adapter->expects($this->at(3))->method('save')->with(array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(2)));
        $adapter->expects($this->at(4))->method('save')->with(array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(3)));
        $adapter->expects($this->at(5))->method('save')->with(array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(4)));
        $adapter->expects($this->at(6))->method('save')->with(array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(4)));
        $adapter->expects($this->at(7))->method('save')->with(array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(5)));
        $adapter->expects($this->at(8))->method('save')->with(array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(6)));
        $adapter->expects($this->at(9))->method('save')->with(array('event2' => $this->_getCurrentDateTime(6), 'event1' => $this->_getCurrentDateTime(6)));
        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTime'))->getMockForAbstractClass();
        $manager->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function() use ($currently) {
            return $currently;
        }));
        $manager->setPersistence(new CM_Clockwork_Persistence($adapter));
        $counter = array(
            '1'  => 0,
            '2'  => 0,
        );
        $this->_createEvent($manager, $currently, new DateInterval('PT1S'), $counter, 'event1');
        $this->_createEvent($manager, $currently, new DateInterval('PT2S'), $counter, 'event2');

        echo 'Start: ' . $this->_getCurrentDateTime()->format('s') . PHP_EOL;
        for ($i = 0; $i < 6; $i++) {
            $currently->add(new DateInterval('PT1S'));
            $manager->runEvents();
        }
    }

    /**
     * @param int $delta
     * @return DateTime
     */
    protected function _getCurrentDateTime($delta = null) {
        $dateTime = new DateTime();
        if ($delta) {
            $dateTime->add(new DateInterval('PT' . $delta . 'S'));
        }
        return $dateTime;
    }

    /**
     * @param CM_Clockwork_Manager $manager
     * @param DateTime             $timeReference
     * @param DateInterval         $interval
     * @param array                $counter
     * @param string               $name
     */
    private function _createEvent(CM_Clockwork_Manager $manager, DateTime $timeReference, DateInterval $interval, &$counter, $name) {
        $callback = function () use (&$counter, $interval) {
            $key = $interval->s;
            $counter[$key]++;
        };
        $event = $this->getMockBuilder('CM_Clockwork_Event')->setMethods(array('_getCurrentDateTime'))->setConstructorArgs(array($name, $interval, new DateTime()))->getMock();
        $event->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function () use ($timeReference) {
            return clone $timeReference;
        }));
        /** @var CM_Clockwork_Event $event */
        $event->registerCallback($callback);
        $manager->registerEvent($event);
    }
}
