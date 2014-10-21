<?php

class CM_Clockwork_Manager {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var CM_Clockwork_Persistence */
    private $_persistence;

    private $_startTime;

    public function __construct() {
        $this->_events = array();
        $this->_persistence = new CM_Clockwork_Persistence_None();
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
            $this->_persistence->setRuntime($event, $this->_getCurrentDateTime());
        }
    }

    /**
     * @param CM_Clockwork_Persistence $persistence
     */
    public function setPersistence(CM_Clockwork_Persistence $persistence) {
        $this->_persistence = $persistence;
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _shouldRun(CM_Clockwork_Event $event) {
        $lastRuntime = $this->_persistence->getLastRuntime($event);
        if ($lastRuntime) {
            $nextExecutionTime = clone $lastRuntime;
            $dateTimeString = $event->getDateTimeString();
            $nextExecutionTime->modify($dateTimeString);
            if ($nextExecutionTime == $this->_getCurrentDateTime()->modify($dateTimeString) ||
                $nextExecutionTime > $this->_getCurrentDateTime()
            ) {
                return false;
            }
            return true;
        }
        $startTime = clone $this->_startTime;
        return $this->_getCurrentDateTime() >= $startTime->modify($event->getDateTimeString());
    }

    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
