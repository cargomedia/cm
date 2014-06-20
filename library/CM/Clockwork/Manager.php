<?php

class CM_Clockwork_Manager {

    /** @var CM_Clockwork_Event[] */
    private $_events;

    /** @var CM_Clockwork_Event[] */
    private $_eventsRunning = array();

    /** @var CM_Clockwork_Persistence */
    private $_persistence;

    public function __construct() {
        $this->_events = array();
        $this->_persistence = new CM_Clockwork_Persistence_Noop();
    }

    /**
     * @param CM_Clockwork_Event $event
     */
    public function registerEvent(CM_Clockwork_Event $event) {
        $this->_events[] = $event;
    }

    /**
     * @param string        $name
     * @param DateInterval  $interval
     * @param callable      $callback
     * @param DateTime|null $nextRun
     */
    public function registerCallback($name, DateInterval $interval, $callback, DateTime $nextRun = null) {
        $event = new CM_Clockwork_Event($name, $interval, $nextRun);
        $event->registerCallback($callback);
        $this->registerEvent($event);
    }

    public function start() {
        while (true) {
            $this->runEvents(true);
            sleep(1);
        }
    }

    public function runEvents($noWaitOnEventExecution = null) {
        $process = CM_Process::getInstance();
        if ($noWaitOnEventExecution) {
            $process->listenForChildren();
        }
        $noWaitOnEventExecution = (boolean) $noWaitOnEventExecution;
        /** @var CM_Clockwork_Event[] $eventsToRun */
        $eventsToRun = array();
        foreach ($this->_events as $event) {
            $lastRuntime = $this->_persistence->getLastRunTime($event);
            if ($event->shouldRun($lastRuntime) && !$this->_isRunning($event)) {
                $eventsToRun[] = $event;
            }
        }
        foreach ($eventsToRun as $event) {
            $this->_markRunning($event);
            $process->fork(function () use ($event) {
                $event->run();
                return array('thisRun' => $this->_getCurrentDateTime(), 'nextRun' => $event->getNextRun());
            }, function (CM_Process_WorkloadResult $result) use ($event) {
                $this->_markStopped($event);
                $event->setNextRun($result->getResult()['nextRun']);
                $this->_persistence->setRuntime($event, $result->getResult()['thisRun']);
            });
        }
        if (!$noWaitOnEventExecution) {
            $process->waitForChildren();
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

    /**
     * @param CM_Clockwork_Event $event
     * @return boolean
     */
    protected function _isRunning(CM_Clockwork_Event $event) {
        return array_key_exists($event->getName(), $this->_eventsRunning);
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
    protected function _markRunning(CM_Clockwork_Event $event) {
        if (!$this->_isRunning($event)) {
            $this->_eventsRunning[$event->getName()] = $event;
        }
    }
}
