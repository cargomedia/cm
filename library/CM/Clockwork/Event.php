<?php

class CM_Clockwork_Event {

    /** @var string */
    private $_dateTimeString;

    /** @var string */
    private $_name;

    /** @var DateTime */
    private $_nextRun;

    /** @var callable[] */
    private $_callbacks;

    /**
     * @param string $name
     * @param string $dateTimeString see http://php.net/manual/en/datetime.formats.php
     */
    public function __construct($name, $dateTimeString) {
        $this->_name = (string) $name;
        $this->_dateTimeString = (string) $dateTimeString;
        $this->_nextRun = $this->_getCurrentDateTime()->modify($dateTimeString);
        $this->_callbacks = [];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param DateTime $lastRuntime
     * @return bool
     */
    public function shouldRun(DateTime $lastRuntime = null) {
        if ($lastRuntime) {
            $nextExecutionTime = clone $lastRuntime;
            $nextExecutionTime->modify($this->_dateTimeString);
            if ($nextExecutionTime == $this->_getCurrentDateTime()->modify($this->_dateTimeString) || $nextExecutionTime > $this->_getCurrentDateTime()) {
                return false;
            }
            return true;
        }
        return $this->_getCurrentDateTime() >= $this->_nextRun;
    }

    /**
     * @param callable $callback
     * @throws CM_Exception_Invalid
     */
    public function registerCallback($callback) {
        if (!is_callable($callback)) {
            throw new CM_Exception_Invalid('$callback needs to be callable');
        }
        $this->_callbacks[] = $callback;
    }

    public function run() {
        foreach ($this->_callbacks as $callback) {
            call_user_func($callback);
        }
        $this->_nextRun = $this->_getCurrentDateTime()->modify($this->_dateTimeString);
    }

    /**
     * @return DateTime
     */
    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
