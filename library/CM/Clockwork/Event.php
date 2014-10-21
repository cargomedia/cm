<?php

class CM_Clockwork_Event {

    /** @var string */
    private $_dateTimeString;

    /** @var string */
    private $_name;

    /** @var callable[] */
    private $_callbacks;

    /**
     * @param string $name
     * @param string $dateTimeString see http://php.net/manual/en/datetime.formats.php
     */
    public function __construct($name, $dateTimeString) {
        $this->_name = (string) $name;
        $this->_dateTimeString = (string) $dateTimeString;
        $this->_callbacks = [];
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getDateTimeString() {
        return $this->_dateTimeString;
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
    }

    /**
     * @return DateTime
     */
    protected function _getCurrentDateTime() {
        return new DateTime();
    }
}
