<?php

class CM_Frontend_JsonSerializable implements JsonSerializable {

    /** @var array */
    protected $_data;

    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @param array $data
     */
    public function setData($data) {
        $this->_data = $data;
    }

    public function __construct(array $data) {
        $this->setData($data);
    }

    function jsonSerialize() {
        return $this->getData();
    }
}
