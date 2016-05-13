<?php

class CM_Log_Context_Extra {

    /** @var array */
    protected $_fields;

    public function __construct() {
        $this->_fields = [];
    }

    /**
     * @param array $fields
     */
    public function set(array $fields) {
        $this->_fields = [];
        foreach ($fields as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function add($key, $value) {
        $key = (string) $key;
        $this->_fields[$key] = $value;
    }

    /**
     * @param CM_Log_Context_Extra $extra
     */
    public function merge(CM_Log_Context_Extra $extra) {
        $this->_fields = array_merge($this->_fields, $extra->_fields);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->_fields;
    }

}
