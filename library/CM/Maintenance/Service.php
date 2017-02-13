<?php

class CM_Maintenance_Service implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Clockwork_Manager */
    private $_clockworkManager;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_clockworkStorage;

    /** @var CM_Maintenance_Event[] */
    private $_eventList = [];

    /** @var CM_EventHandler_EventHandler */
    private $_eventHandler;

    /**
     * @param CM_Clockwork_Storage_Abstract|null $clockworkStorage
     */
    public function __construct(CM_Clockwork_Storage_Abstract $clockworkStorage = null) {
        if (null === $clockworkStorage) {
            $clockworkStorage = new CM_Clockwork_Storage_Memory();
        }
        $this->_clockworkStorage = $clockworkStorage;
        $this->_eventHandler = new CM_EventHandler_EventHandler();
    }

    /**
     * @param string                    $eventName
     * @param CM_Clockwork_Event_Result $result
     */
    public function handleClockworkEventResult($eventName, CM_Clockwork_Event_Result $result) {
        $event = $this->_getEvent($eventName);
        $this->_getClockworkManager()->handleEventResult($event->getClockworkEvent(), $result);
    }

    public function runEvents() {
        $this->_getClockworkManager()->runEvents();
    }

    /**
     * @param string  $name
     * @param string  $dateTimeString
     * @param Closure $callback
     * @throws CM_Exception_Invalid
     */
    public function registerEvent($name, $dateTimeString, Closure $callback) {
        $name = (string) $name;
        $dateTimeString = (string) $dateTimeString;

        $duplicateEventName = \Functional\some($this->_eventList, function (CM_Maintenance_Event $event) use ($name) {
            return $event->getName() == $name;
        });
        if ($duplicateEventName) {
            throw new CM_Exception_Invalid('Duplicate event-name', null, ['event' => $name]);
        }
        $this->_eventList[] = new CM_Maintenance_Event($name, $dateTimeString, $callback);
    }

    /**
     * @param string        $eventName
     * @param DateTime|null $lastRuntime
     */
    public function runEvent($eventName, DateTime $lastRuntime = null) {
        $eventName = (string) $eventName;
        $event = $this->_getEvent($eventName);

        // TODO: remove debug-code
        $timeStart = (new DateTime())->format('H:i:s');
        $lastTime = $lastRuntime ? $lastRuntime->format('H:i:s') : 'never';
        (new CM_File(DIR_ROOT . '/log.txt'))->appendLine($timeStart . ' running: ' . $eventName . ' last run: ' . $lastTime);

        $event->runCallback($lastRuntime);

        // TODO: remove debug-code
        $timeEnd = (new DateTime())->format('H:i:s');
        (new CM_File(DIR_ROOT . '/log.txt'))->appendLine($timeEnd . ' stopping: ' . $eventName . ' started: ' . $timeStart);
    }

    /**
     * @return CM_Clockwork_Manager
     */
    private function _getClockworkManager() {
        if (!$this->_clockworkManager) {
            $this->_clockworkManager = new CM_Clockwork_Manager($this->_eventHandler);
            $this->_clockworkManager->setStorage($this->_clockworkStorage);
            $this->_registerClockworkEvents();
        }
        return $this->_clockworkManager;
    }

    private function _registerClockworkEvents() {
        foreach ($this->_eventList as $event) {
            $this->_clockworkManager->registerEvent($event->getClockworkEvent());
            $this->_eventHandler->bind($event->getName(), function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
                $job = new CM_Maintenance_RunEventJob();
                $job->setServiceManager($this->getServiceManager());
                $lastRuntime = $status->getLastRuntime();
                if (null !== $lastRuntime) {
                    $lastRuntime = $lastRuntime->getTimestamp();
                }
                $job->queue(['event' => $event->getName(), 'lastRuntime' => $lastRuntime]);
            });
        }
    }

    /**
     * @param string $eventName
     * @return CM_Maintenance_Event
     * @throws CM_Exception_Invalid
     */
    private function _getEvent($eventName) {
        $eventName = (string) $eventName;
        $event = \Functional\first($this->_eventList, function (CM_Maintenance_Event $event) use ($eventName) {
            return $event->getName() === $eventName;
        });
        if (null === $event) {
            throw new CM_Exception_Invalid('Event not found', null, ['event' => $eventName]);
        }
        return $event;
    }

}
