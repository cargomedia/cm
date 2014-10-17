<?php

class CM_Clockwork_Manager {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var CM_Clockwork_Persistence */
    private $_persistence;

    public function __construct() {
        $this->_events = array();
        $this->_persistence = new CM_Clockwork_Persistence_None();
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
    }

    /**
     * @param string        $name
     * @param string        $dateTimeString
     * @param callable      $callback
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
            $lastRuntime = $this->_persistence->getLastRuntime($event);
            if ($event->shouldRun($lastRuntime)) {
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

    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
