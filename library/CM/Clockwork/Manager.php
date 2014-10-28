<?php

class CM_Clockwork_Manager extends CM_Class_Abstract {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_storage;

    /** @var DateTime */
    private $_startTime;

    /** @var DateTimeZone */
    private $_timeZone;

    public function __construct() {
        $this->_events = array();
        $this->_storage = new CM_Clockwork_Storage_Memory();
        $this->_timeZone = new DateTimeZone('UTC');
        $this->_startTime = $this->_getCurrentDateTime();
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
    }

    /**
     * @param DateTimeZone $timeZone
     */
    public function setTimeZone(DateTimeZone $timeZone) {
        $this->_timeZone = $timeZone;
        $this->_startTime->setTimezone($this->_timeZone);
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
        $dateTimeString = $event->getDateTimeString();

        if ($lastRuntime) {
            $lastRuntime->setTimezone($this->_timeZone);
            $nextExecutionTime = clone $lastRuntime;
        } else {
            $nextExecutionTime = clone $this->_startTime;
        }

        $nextExecutionTime = $this->_getDSTAgnosticDateTime($nextExecutionTime);
        $nextExecutionTime->modify($dateTimeString);
        if ($lastRuntime) {
            $executedInCurrentTimeframe = $nextExecutionTime ==
                $this->_getDSTAgnosticDateTime($this->_getCurrentDateTime(), $lastRuntime->getOffset())->modify($dateTimeString);
            if ($executedInCurrentTimeframe && $lastRuntime >= $nextExecutionTime) {
                return false;
            }
        }
        return $this->_getCurrentDateTime() >= $nextExecutionTime;
    }

    /**
     * @param DateTime $dateTime
     * @param int|null $offset
     * @return DateTime
     *
     * Workaround for buggy DateTime modify() behaviour around dst-change see https://bugs.php.net/bug.php?id=51051
     */
    protected function _getDSTAgnosticDateTime(DateTime $dateTime, $offset = null) {
        $offset = $offset ? $offset : $dateTime->getOffset();
        $offsetHours = $offset / 3600;
        $dateString = $dateTime->format('Y-m-d ') . ' ' . $dateTime->format('H:i:s') . ($offsetHours >= 0 ? ' +' : '') . $offsetHours;
        return new DateTime($dateString);
    }

    /**
     * @return DateTime
     */
    protected function _getCurrentDateTime() {
        return $this->_getCurrentDateTimeUTC()->setTimezone($this->_timeZone);
    }

    protected function _getCurrentDateTimeUTC() {
        return new DateTime('now', new DateTimeZone('UTC'));
    }
}
