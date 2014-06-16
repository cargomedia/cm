<?php

class CM_Clockwork_Event {

    /** @var DateInterval */
    private $_interval;

    /** @var string */
    private $_name;

    /** @var DateTime */
    private $_nextRun;

    /** @var callable[] */
    private $_callbacks;

    /**
     * @param string        $name
     * @param DateInterval  $interval
     * @param DateTime|null $nextRun
     */
    public function __construct($name, DateInterval $interval, DateTime $nextRun = null) {
        $this->_name = (string) $name;
        $this->_interval = $interval;
        if (null === $nextRun) {
            $nextRun = $this->_getCurrentDateTime();
            $nextRun->add($interval);
        }
        $this->_nextRun = $nextRun;
        $this->_callbacks = array();
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
            $lastRuntime = clone $lastRuntime;
            if ($lastRuntime->add($this->_interval) > $this->_getCurrentDateTime()) {
                return false;
            }
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
        $nextRun = $this->_getCurrentDateTime();
        $nextRun->add($this->_interval);
        $this->_nextRun = $nextRun;
    }

    /**
     * @return DateTime
     */
    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
