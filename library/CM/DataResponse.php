<?php

class CM_DataResponse extends CM_Class_Abstract {

    /** @var array */
    protected $_data = array();

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * @param array $data
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->_data;
    }
}
