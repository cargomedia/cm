<?php

class CM_Clockwork_Manager {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_storage;

   /** @var DateTime */
    private $_startTime;

    public function __construct() {
        $this->_events = array();
        $this->_storage = new CM_Clockwork_Storage_Memory();
        $this->_startTime = $this->_getCurrentDateTime();
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
    }

    /**
     * @param string   $name
     * @param string   $dateTimeString
     * @param callable $callback
     */
    public function registerCallback($name, $dateTimeString, $callback) {
        $event = new CM_Clockwork_Event($name, $dateTimeString);
        $event->registerCallback($callback);
        $this->registerEvent($event);
    }

    public function start() {
        while (true) {
            $this->runEvents();
            sleep(1);
        }
    }

    public function runEvents() {
        /** @var CM_Clockwork_Event[] $eventsToRun */
        $eventsToRun = array();
        foreach ($this->_events as $event) {
            if ($this->_shouldRun($event)) {
                $eventsToRun[] = $event;
            }
        }
        foreach ($eventsToRun as $event) {
            $event->run();
            $this->_storage->setRuntime($event, $this->_getCurrentDateTime());
        }
    }

    /**
     * @param CM_Clockwork_Storage_Abstract $persistence
     */
    public function setStorage(CM_Clockwork_Storage_Abstract $persistence) {
        $this->_storage = $persistence;
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _shouldRun(CM_Clockwork_Event $event) {
        $lastRuntime = $this->_storage->getLastRuntime($event);
        if ($lastRuntime) {
            $nextExecutionTime = clone $lastRuntime;
            $dateTimeString = $event->getDateTimeString();
            $nextExecutionTime->modify($dateTimeString);
            if (($nextExecutionTime == $this->_getCurrentDateTime()->modify($dateTimeString) && $lastRuntime >= $nextExecutionTime) ||
                $nextExecutionTime > $this->_getCurrentDateTime()
            ) {
                return false;
            }
            return true;
        }
        $startTime = clone $this->_startTime;
        return $this->_getCurrentDateTime() >= $startTime->modify($event->getDateTimeString());
    }

    /**
     * @return DateTime
     */
    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
