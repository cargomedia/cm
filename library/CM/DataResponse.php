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

    /**
     * @param string $key
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function get($key) {
        if (!array_key_exists($key, $this->_data)) {
            throw new CM_Exception_Invalid('Key `' . $key . '` not found');
        }
        return $this->_data[$key];
    }
}
