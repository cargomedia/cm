<?php

class CM_Clockwork_Event {

    /** @var string */
    private $_dateTimeString;

    /** @var string */
    private $_name;

    /**
     * @param string $name
     * @param string $dateTimeString see http://php.net/manual/en/datetime.formats.php
     */
    public function __construct($name, $dateTimeString) {
        $this->_name = (string) $name;
        $this->_dateTimeString = (string) $dateTimeString;
    }

    /**
     * @return string
     */
    public function getDateTimeString() {
        return $this->_dateTimeString;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }
}
