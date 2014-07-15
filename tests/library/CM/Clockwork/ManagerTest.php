<?php
require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php'; // Bootstrap the test explicitly when running in a separate process
use Mocka\Mocka;

class CM_Clockwork_ManagerTest extends CMTest_TestCase {

    /** @var resource */
    public static $_file;

    public static function setupBeforeClass() {
        parent::setUpBeforeClass();
        self::$_file = tmpfile();
    }

    public static function tearDownAfterClass() {
        fclose(self::$_file);
        parent::tearDownAfterClass();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRunEventsFor() {
        $currently = new DateTime();

        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTime'))->getMockForAbstractClass();
        $manager->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function() use ($currently) {
            return $currently;
        }));
        /** @var CM_Clockwork_Manager $manager */

        $this->_createEvent($manager, $currently, new DateInterval('PT1S'), 'event1');
        $this->_createEvent($manager, $currently, new DateInterval('PT2S'), 'event2');
        $this->_createEvent($manager, $currently, new DateInterval('PT5S'), 'event3');
        $this->_createEvent($manager, $currently, new DateInterval('PT15S'), 'event4');

        for($i = 1; $i <= 20; $i++) {
            $manager->runEvents(true);
            $currently->add(new DateInterval('PT1S'));
            usleep(100 * 1000);
        }
        CM_Process::getInstance()->waitForChildren();
        $this->assertSame(array(
            'event1'  => 20,
            'event2'  => 10,
            'event3'  => 4,
            'event4' => 2,
        ), $this->getCounter());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRunEventsPersistence() {
        $currently = new DateTime();
        $context = 'foo';
        $adapter = $this->getMockBuilder('CM_Clockwork_PersistenceAdapter_Abstract')->disableOriginalConstructor()->setMethods(array('load', 'save'))
            ->getMockForAbstractClass();
        $adapter->expects($this->any())->method('load')->will($this->returnValue(array('event2' => $this->_getCurrentDateTime())));
        $adapter->expects($this->at(1))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(), 'event1' => $this->_getCurrentDateTime(1)));
        $adapter->expects($this->at(2))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(), 'event1' => $this->_getCurrentDateTime(2)));
        $adapter->expects($this->at(3))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(2)));
        $adapter->expects($this->at(4))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(3)));
        $adapter->expects($this->at(5))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(2), 'event1' => $this->_getCurrentDateTime(4)));
        $adapter->expects($this->at(6))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(4)));
        $adapter->expects($this->at(7))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(5)));
        $adapter->expects($this->at(8))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(4), 'event1' => $this->_getCurrentDateTime(6)));
        $adapter->expects($this->at(9))->method('save')->with($context, array('event2' => $this->_getCurrentDateTime(6), 'event1' => $this->_getCurrentDateTime(6)));
        $manager = $this->getMockBuilder('CM_Clockwork_Manager')->setMethods(array('_getCurrentDateTime'))->getMockForAbstractClass();
        $manager->expects($this->any())->method('_getCurrentDateTime')->will($this->returnCallback(function() use ($currently) {
            return $currently;
        }));
        /** @var CM_Clockwork_Manager $manager */
        $manager->setPersistence(new CM_Clockwork_Persistence($context, $adapter));
        $this->_createEvent($manager, $currently, new DateInterval('PT1S'), 'event1');
        $this->_createEvent($manager, $currently, new DateInterval('PT2S'), 'event2');

        for ($i = 1; $i <= 6; $i++) {
            $currently->add(new DateInterval('PT1S'));
            $manager->runEvents(true);
            usleep(100 * 1000);
        }
        CM_Process::getInstance()->waitForChildren();
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
     * @param DateTime             $start
     * @param DateInterval         $interval
     * @param string               $name
     */
    private function _createEvent(CM_Clockwork_Manager $manager, DateTime $start, DateInterval $interval, $name) {
        $callback = function () use ($name) {
            CM_Clockwork_ManagerTest::incCounter($name);
        };
        $mocka = new Mocka();
        $event = $mocka->mockObject('CM_Clockwork_Event', array($name, $interval, $start));
        $event->mockMethod('_getCurrentDateTime')->set(function() use ($start) {
            return clone $start;
        });
        /** @var CM_Clockwork_Event $event */
        $event->registerCallback($callback);
        $manager->registerEvent($event);
    }

    /**
     * @param string $message
     */
    public static function writeln($message) {
        print "$message\n";
        fwrite(self::$_file, "$message\n");
    }

    public static function incCounter($name) {
        fwrite(self::$_file, "$name\n");
    }

    public function getCounter() {
        rewind(self::$_file);
        $fileContent = fread(self::$_file, 8192);
        //        return unserialize($fileContent);
        $counter = array();
        foreach (explode("\n", $fileContent) as $line) {
            if (strlen($line)) {
                $counter[$line] = isset($counter[$line]) ? $counter[$line] + 1 : 1;
            }
        }
        ksort($counter);
        return $counter;
    }
}
