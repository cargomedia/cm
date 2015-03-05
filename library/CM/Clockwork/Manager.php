<?php

class CM_Clockwork_Manager {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var DateTime */
    private $_startTime;

    /** @var CM_Clockwork_Storage_Abstract */
    private $_storage;

    /** @var DateTimeZone */
    private $_timeZone;

    /** @var array */
    private $_eventsRunning = [];

    public function __construct() {
        $this->_events = array();
        $this->_storage = new CM_Clockwork_Storage_Memory();
        $this->_timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $this->_startTime = $this->_getCurrentDateTimeUTC();
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

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
    }

    public function runEvents() {
        $process = $this->_getProcess();
        foreach ($this->_events as $event) {
            if (!$this->_isRunning($event)) {
                if ($this->_shouldRun($event)) {
                    $this->_runEvent($event);
                } elseif (null === $this->_storage->getLastRuntime($event) && $this->_isIntervalEvent($event)) {
                    $this->_storage->setRuntime($event, $this->_startTime);
                }
            }
        }
        $resultList = $process->listenForChildren();
        foreach ($resultList as $identifier => $result) {
            $event = $this->_getRunningEvent($identifier);
            $this->_markStopped($event);
            $this->_storage->setRuntime($event, $this->_getCurrentDateTime());
        }
    }

    /**
     * @param CM_Clockwork_Storage_Abstract $storage
     */
    public function setStorage(CM_Clockwork_Storage_Abstract $storage) {
        $this->_storage = $storage;
    }

    /**
     * @param DateTimeZone $timeZone
     */
    public function setTimeZone(DateTimeZone $timeZone) {
        $this->_timeZone = $timeZone;
    }

    public function start() {
        while (true) {
            $this->runEvents();
            sleep(1);
        }
    }

    /**
     * @param int $identifier
     * @return CM_Clockwork_Event
     * @throws CM_Exception
     */
    protected function _getRunningEvent($identifier) {
        $eventName = array_search($identifier, \Functional\pluck($this->_eventsRunning, 'identifier'));
        if (false === $eventName) {
            throw new CM_Exception('Could not find event', ['identifier' => $identifier]);
        }
        return $this->_eventsRunning[$eventName]['event'];
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _shouldRun(CM_Clockwork_Event $event) {
        $lastRuntime = $this->_storage->getLastRuntime($event);
        $base = $lastRuntime ?: clone $this->_startTime;
        $dateTimeString = $event->getDateTimeString();
        if (!$this->_isIntervalEvent($event)) {     // do not set timezone for interval-based events due to buggy behaviour with timezones that use
            $base->setTimezone($this->_timeZone);   // daylight saving time, see https://bugs.php.net/bug.php?id=51051
        }
        $nextExecutionTime = clone $base;
        $nextExecutionTime->modify($dateTimeString);
        if ($lastRuntime) {
            if ($nextExecutionTime <= $base) {
                $nextExecutionTime = $this->_getCurrentDateTime()->modify($dateTimeString);
            }
            $shouldRun = $nextExecutionTime > $base && $this->_getCurrentDateTime() >= $nextExecutionTime;
        } else {
            if ($nextExecutionTime < $base) {
                $nextExecutionTime = $this->_getCurrentDateTime()->modify($dateTimeString);
            }
            $shouldRun = $nextExecutionTime >= $base && $this->_getCurrentDateTime() >= $nextExecutionTime;
        }
        return $shouldRun;
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

    /**
     * @return CM_Process
     */
    protected function _getProcess() {
        return CM_Process::getInstance();
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _isIntervalEvent(CM_Clockwork_Event $event) {
        $dateTimeString = $event->getDateTimeString();
        $date = new DateTime();
        $dateModified = new DateTime();
        $dateModified->modify($dateTimeString);
        return $date->modify($dateTimeString) != $dateModified->modify($dateTimeString);
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _isRunning(CM_Clockwork_Event $event) {
        return array_key_exists($event->getName(), $this->_eventsRunning);
    }

    /**
     * @param CM_Clockwork_Event $event
     * @param int                $identifier
     */
    protected function _markRunning(CM_Clockwork_Event $event, $identifier) {
        if (!$this->_isRunning($event)) {
            $this->_eventsRunning[$event->getName()] = ['event' => $event, 'identifier' => $identifier];
        }
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    protected function _markStopped(CM_Clockwork_Event $event) {
        if ($this->_isRunning($event)) {
            unset($this->_eventsRunning[$event->getName()]);
        }
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    protected function _runEvent(CM_Clockwork_Event $event) {
        $process = $this->_getProcess();
        $identifier = $process->fork(function () use ($event) {
            $event->run();
        });
        $this->_markRunning($event, $identifier);
    }
}
