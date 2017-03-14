<?php

class CM_Maintenance_Event {

    /** @var Closure */
    private $_callback;

    /**
     * @param string  $name
     * @param string  $dateTimeString
     * @param Closure $callback
     * @see CM_Clockwork_Event::__construct()
     */
    public function __construct($name, $dateTimeString, Closure $callback) {
        $this->_clockworkEvent = new CM_Clockwork_Event($name, $dateTimeString);
        $this->_callback = $callback;
    }

    /**
     * @return CM_Clockwork_Event
     */
    public function getClockworkEvent() {
        return $this->_clockworkEvent;
    }

    /**
     * @return string
     */
    public function getDateTimeString() {
        return $this->_clockworkEvent->getDateTimeString();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_clockworkEvent->getName();
    }

    /**
     * @param DateTime|null $lastRuntime
     */
    public function runCallback(DateTime $lastRuntime = null) {
        call_user_func($this->_callback, $lastRuntime);
    }
}
