<?php

class CM_Maintenance_Service implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Clockwork_Manager */
    private $_clockworkManager;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_clockworkStorage;

    /** @var CM_Maintenance_Event[] */
    private $_eventMap = [];

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

        if ($this->_eventExists($name)) {
            throw new CM_Exception_Invalid('Duplicate event-name', null, ['event' => $name]);
        }
        $this->_eventMap[$name] = new CM_Maintenance_Event($name, $dateTimeString, $callback);
    }

    /**
     * @param string        $eventName
     * @param DateTime|null $lastRuntime
     */
    public function runEvent($eventName, DateTime $lastRuntime = null) {
        $eventName = (string) $eventName;
        $event = $this->_getEvent($eventName);
        $event->runCallback($lastRuntime);
    }

    /**
     * @return CM_Clockwork_Manager
     */
    protected function _getClockworkManager() {
        if (!$this->_clockworkManager) {
            $this->_clockworkManager = new CM_Clockwork_Manager($this->_eventHandler);
            $this->_clockworkManager->setStorage($this->_clockworkStorage);
            $this->_registerClockworkEvents();
        }
        return $this->_clockworkManager;
    }

    /**
     * @param string $eventName
     * @return boolean
     */
    private function _eventExists($eventName) {
        return (array_key_exists((string) $eventName, $this->_eventMap));
    }

    private function _registerClockworkEvents() {
        foreach ($this->_eventMap as $name => $event) {
            $this->_clockworkManager->registerEvent($event->getClockworkEvent());
            $this->_eventHandler->bind($event->getName(), function (CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
                $lastRuntime = $status->getLastRuntime();
                if (null !== $lastRuntime) {
                    $lastRuntime = $lastRuntime->getTimestamp();
                }
                $job = new CM_Maintenance_RunEventJob(CM_Params::factory([
                    'event' => $event->getName(), 
                    'lastRuntime' => $lastRuntime
                ]));
                $this->getServiceManager()->getJobQueue()->queue($job);
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
        if (!$this->_eventExists($eventName)) {
            throw new CM_Exception_Invalid('Event not found', null, ['event' => $eventName]);
        }
        return $this->_eventMap[$eventName];
    }

}
