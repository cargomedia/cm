<?php

class CM_Clockwork_Manager extends CM_Service_ManagerAware {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var DateTime */
    private $_startTime;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_storage;

    /** @var DateTimeZone */
    private $_timeZone;

    public function __construct() {
        $this->_events = array();
        $this->_storage = new CM_Clockwork_Storage_Memory();
        $this->_timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $this->_startTime = $this->_getCurrentDateTime();
    }

    /**
     * @param string      $name
     * @param string      $dateTimeString
     * @param string|null $timeframe
     * @param callable    $callback
     */
    public function registerCallback($name, $dateTimeString, $callback, $timeframe = null) {
        $event = new CM_Clockwork_Event($name, $dateTimeString, $timeframe);
        $event->registerCallback($callback);
        $this->registerEvent($event);
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
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
     * @param CM_Clockwork_Storage_Abstract $storage
     */
    public function setStorage(CM_Clockwork_Storage_Abstract $storage) {
        $this->_storage = $storage;
        $this->_storage->setServiceManager($this->getServiceManager());
    }

    /**
     * @param DateTimeZone $timeZone
     */
    public function setTimeZone(DateTimeZone $timeZone) {
        $this->_timeZone = $timeZone;
        $this->_startTime->setTimezone($this->_timeZone);
    }

    public function start() {
        while (true) {
            $this->runEvents();
            sleep(1);
        }
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _shouldRun(CM_Clockwork_Event $event) {
        $lastRuntime = $this->_storage->getLastRuntime($event);
        $dateTimeString = $event->getDateTimeString();
        $timeframe = $event->getTimeframe();
        if ($lastRuntime) {
            $lastRuntime->setTimezone($this->_timeZone);
            $nextExecutionTime = clone $lastRuntime;
            if ($timeframe && $lastRuntime >= $nextExecutionTime->modify($dateTimeString)) {
                $nextExecutionTime->modify('next ' . $timeframe);
            }
        } else {
            $nextExecutionTime = clone $this->_startTime;
            if ($timeframe && $this->_startTime > $nextExecutionTime->modify($dateTimeString)) {
                $nextExecutionTime->modify('next ' . $timeframe);
            }
        }
        if (!$timeframe) {
            $nextExecutionTime = $this->_getDSTAgnosticDateTime($nextExecutionTime);
        }
        $nextExecutionTime->modify($dateTimeString);
        return $this->_getCurrentDateTime() >= $nextExecutionTime;
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime
     *
     * Workaround for buggy DateTime modify() behaviour around dst-change see https://bugs.php.net/bug.php?id=51051
     */
    protected function _getDSTAgnosticDateTime(DateTime $dateTime) {
        $offsetHours = $dateTime->getOffset() / 3600;
        $dateString = $dateTime->format('Y-m-d H:i:s') . ($offsetHours >= 0 ? ' +' : '') . $offsetHours;
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
