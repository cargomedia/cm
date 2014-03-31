<?php

class CM_PagingSource_MongoDB extends CM_PagingSource_Abstract {

    private $_fields, $_collection, $_query;
    private $_fieldFilter = null;
    private $_processItemCallback = null;

    /** @var array */
    private $_parameters = array();

    /**
     * @param null|array    $fields
     * @param string        $collection
     * @param array         $query
     * @param array         $parameters
     * @param null|function $processItemCallback
     */
    function __construct($fields, $collection, $query, $parameters = array(), $processItemCallback = null) {
        $this->_collection = $collection;
        $this->_query = $query;
        $this->_fields = $fields;
        $this->_parameters = $parameters;
        $this->_processItemCallback = $processItemCallback;

        if ($this->_fields) {
            $this->_fieldFilter = array_flip($this->_fields);
        }
    }

    public function getCount($offset = null, $count = null) {
        $mdb = CM_MongoDB_Client::getInstance();
        return $mdb->find($this->_collection, $this->_query)->count();
    }

    public function getItems($offset = null, $count = null) {
        $mdb = CM_MongoDB_Client::getInstance();
        $result = array();
        $cursor = $mdb->find($this->_collection, $this->_query);
        foreach ($cursor as $item) {
            $item['id'] = $item['_id'];
            if ($this->_fieldFilter) {
                $result[] = array_intersect_key($item, $this->_fieldFilter);
            } else {
                $result[] = $item;
            }
        }

        if ($this->_processItemCallback !== null) {
            $result = array_map($this->_processItemCallback, $result);
        }

        return $result;
    }

    protected function _cacheKeyBase() {
        return array($this->_fields, $this->_collection, $this->_query);
    }

    public function getStalenessChance() {
        return 0.01;
    }
}
