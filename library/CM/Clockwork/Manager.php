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

    /** @var CM_EventHandler_EventHandlerInterface */
    private $_eventHandler;

    /** @var CM_Clockwork_Event_Status[] */
    private $_statusList = [];

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     */
    public function __construct(CM_EventHandler_EventHandlerInterface $eventHandler) {
        $this->_events = array();
        $this->_storage = new CM_Clockwork_Storage_Memory();
        $this->_timeZone = CM_Bootloader::getInstance()->getTimeZone();
        $this->_startTime = $this->_getCurrentDateTimeUTC();
        $this->_eventHandler = $eventHandler;
    }

    /**
     * @param CM_Clockwork_Event $event
     * @throws CM_Exception
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $eventName = $event->getName();
        if ($this->_eventExists($eventName)) {
            throw new CM_Exception('Duplicate event-name', null, ['eventName' => $eventName]);
        }
        $this->_events[] = $event;
    }

    public function runEvents() {
        $this->_storage->fetchData();
        foreach ($this->_events as $event) {
            $status = $this->_getStatus($event);

            // TODO: remove debug-code
            $time = $this->_getCurrentDateTimeUTC()->format('H:i:s');
            echo "{$time} event: {$event->getName()} {$status}\n";

            if (!$status->isRunning()) {
                if ($this->_shouldRun($event, $status)) {
                    $this->_runEvent($event, $status);
                } elseif (null === $status->getLastRuntime() && $this->_isIntervalEvent($event)) {
                    $status->setLastRuntime($this->_startTime);
                    $this->_storage->setStatus($event, $status);
                }
            }
        }
    }

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Result $result
     */
    public function handleEventResult(CM_Clockwork_Event $event, CM_Clockwork_Event_Result $result) {
        $this->_checkEventExists($event->getName());
        if ($result->isSuccessful()) {
            $this->setCompleted($event);
        } else {
            $this->setStopped($event);
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

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Status $status
     * @return boolean
     */
    protected function _shouldRun(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $lastRuntime = $status->getLastRuntime();
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
     * @param string $eventName
     * @throws CM_Exception_Invalid
     */
    protected function _checkEventExists($eventName) {
        if (!$this->_eventExists($eventName)) {
            throw new CM_Exception_Invalid('Event does not exist', null, ['event' => $eventName]);
        }
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
     * @param DateTime           $startTime
     * @throws CM_Exception_Invalid
     */
    public function setRunning(CM_Clockwork_Event $event, DateTime $startTime) {
        $eventName = $event->getName();
        $this->_checkEventExists($eventName);
        $this->_storage->fetchData();
        $status = $this->_storage->getStatus($event);
        if ($status->isRunning()) {
            throw new CM_Exception_Invalid('Event is already running', null, ['eventName' => $eventName]);
        }
        $status->setRunning(true)->setLastStartTime($startTime);
        $this->_storage->setStatus($event, $status);
    }

    /**
     * @param CM_Clockwork_Event $event
     * @throws CM_Exception_Invalid
     */
    public function setStopped(CM_Clockwork_Event $event) {
        $eventName = $event->getName();
        $this->_checkEventExists($eventName);
        $this->_storage->fetchData();
        $status = $this->_storage->getStatus($event);
        if (!$status->isRunning()) {
            throw new CM_Exception_Invalid('Cannot stop event. Event is not running.', null, ['eventName' => $event->getName()]);
        }
        $status->setRunning(false);
        $this->_storage->setStatus($event, $status);
    }

    /**
     * @param CM_Clockwork_Event $event
     * @throws CM_Exception_Invalid
     */
    public function setCompleted(CM_Clockwork_Event $event) {
        $eventName = $event->getName();
        $this->_checkEventExists($eventName);
        $this->_storage->fetchData();
        $status = $this->_storage->getStatus($event);
        $status->setRunning(false)->setLastRuntime($status->getLastStartTime());
        $this->_storage->setStatus($event, $status);
    }

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Status $status
     */
    protected function _runEvent(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $startTime = $this->_getCurrentDateTimeUTC();
        $status->setRunning(true)->setLastStartTime($startTime);
        $this->_storage->setStatus($event, $status);
        $this->_eventHandler->trigger($event->getName(), $event, $status);
    }

    /**
     * @param string $eventName
     * @return boolean
     */
    protected function _eventExists($eventName) {
        $eventName = (string) $eventName;
        $duplicateEventName = \Functional\some($this->_events, function (CM_Clockwork_Event $event) use ($eventName) {
            return $event->getName() === $eventName;
        });
        return $duplicateEventName;
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return CM_Clockwork_Event_Status
     */
    protected function _getStatus(CM_Clockwork_Event $event) {
        $status = $this->_storage->getStatus($event);
        if (!array_key_exists($event->getName(), $this->_statusList)) {
            if ($status->isRunning()) {
                $status->setRunning(false);
                $this->_storage->setStatus($event, $status);
            }
            $this->_statusList[$event->getName()] = $status;
        }
        return $status;
    }

}
