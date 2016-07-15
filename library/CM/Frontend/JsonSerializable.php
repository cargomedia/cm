<?php

class CM_Frontend_JsonSerializable implements JsonSerializable {

    /** @var array */
    protected $_data;

    /**
     * @param array|null $data
     */
    public function __construct(array $data = null) {
        $this->setData(null === $data ? [] : $data);
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    function jsonSerialize() {
        return $this->getData();
    }
}
