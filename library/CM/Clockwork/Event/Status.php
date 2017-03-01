<?php

class CM_Clockwork_Event_Status {

    /** @var boolean */
    private $_isRunning = false;

    /** @var DateTime|null */
    private $_lastRuntime;

    /** @var DateTime|null */
    private $_lastStartTime;

    /**
     * @return DateTime|null
     */
    public function getLastRuntime() {
        return $this->_lastRuntime;
    }

    /**
     * @param DateTime|null $lastRuntime
     * @return CM_Clockwork_Event_Status
     */
    public function setLastRuntime(DateTime $lastRuntime = null) {
        $this->_lastRuntime = $lastRuntime;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastStartTime() {
        return $this->_lastStartTime;
    }

    /**
     * @param DateTime|null $lastStartTime
     * @return CM_Clockwork_Event_Status
     */
    public function setLastStartTime(DateTime $lastStartTime = null) {
        $this->_lastStartTime = $lastStartTime;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRunning() {
        return $this->_isRunning;
    }

    /**
     * @param boolean $running
     * @return $this
     */
    public function setRunning($running) {
        $this->_isRunning = (boolean) $running;
        return $this;
    }

    public function __toString() {
        // TODO: remove debug-code
        $lastRun = $this->getLastRuntime() ? $this->getLastRuntime()->format('H:i:s') : 'NULL';
        $lastStart = $this->getLastStartTime() ? $this->getLastStartTime()->format('H:i:s') : 'NULL';
        $isRunning = $this->isRunning() ? 'true' : 'false';
        return "lastRun: {$lastRun} lastStart: {$lastStart} running: {$isRunning}";
    }

    function __clone() {
        if ($this->_lastRuntime) {
            $this->_lastRuntime = clone $this->_lastRuntime;
        }
        if ($this->_lastStartTime) {
            $this->_lastStartTime = clone $this->_lastStartTime;
        }
    }

}
