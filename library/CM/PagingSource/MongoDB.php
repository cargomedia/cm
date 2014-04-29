<?php

class CM_PagingSource_MongoDB extends CM_PagingSource_Abstract {

    private $_fields, $_collection, $_query;
    private $_processItemCallback = null;

    /**
     * @param  array|null $fields Array of field which to include/exclude, see http://docs.mongodb.org/manual/reference/method/db.collection.find/#projections
     * @param  string     $collection
     * @param  array      $query
     */
    public function __construct($fields, $collection, $query) {
        $this->_fields = (array) $fields;
        $this->_collection = (string) $collection;
        $this->_query = (array) $query;
    }

    public function getCount($offset = null, $count = null) {
        $mongoDb = CM_Services::getInstance()->getMongoDB();

        return $mongoDb->count($this->_collection, $this->_query);
    }

    public function getItems($offset = null, $count = null) {
        $mdb = CM_Services::getInstance()->getMongoDB();
        $result = array();
        $cursor = $mdb->find($this->_collection, $this->_query, $this->_fields);
        foreach ($cursor as $item) {
            $item['id'] = $item['_id'];
            $result[] = $item;
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
