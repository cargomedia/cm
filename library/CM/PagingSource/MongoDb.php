<?php

class CM_PagingSource_MongoDb extends CM_PagingSource_Abstract {

    /** @var string */
    private $_collection;

    /** @var array */
    private $_criteria;

    /** @var array */
    private $_projection;

    /**
     * @param  string     $collection
     * @param  array|null $criteria
     * @param  array|null $projection http://docs.mongodb.org/manual/reference/method/db.collection.find/#projections
     */
    public function __construct($collection, array $criteria = null, array $projection = null) {
        $this->_collection = (string) $collection;
        $this->_criteria = (array) $criteria;
        $this->_projection = (array) $projection;
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @return int
     */
    public function getCount($offset = null, $limit = null) {
        $cacheKey = array('count');
        if (($limit = $this->_cacheGet($cacheKey)) === false) {
            $mongoDb = CM_ServiceManager::getInstance()->getMongoDb();
            $limit = $mongoDb->count($this->_collection, $this->_criteria, $limit, $offset);
            $this->_cacheSet($cacheKey, $limit);
        } else {
            CM_Debug::getInstance()->incStats('mongoCacheHit', 'getItems()');
        }
        return $limit;
    }

    /**
     * @param int|null $offset
     * @param int|null $count
     * @return array
     */
    public function getItems($offset = null, $count = null) {
        $cacheKey = array('items', $offset, $count);
        if (($items = $this->_cacheGet($cacheKey)) === false) {
            $mongoDb = CM_ServiceManager::getInstance()->getMongoDb();
            $cursor = $mongoDb->find($this->_collection, $this->_criteria, $this->_projection);

            if (null !== $offset) {
                $cursor->skip($offset);
            }
            if (null !== $count) {
                $cursor->limit($count);
            }
            $items = array();
            foreach ($cursor as $item) {
                $items[] = $item;
            }
            $this->_cacheSet($cacheKey, $items);
        } else {
            CM_Debug::getInstance()->incStats('mongoCacheHit', 'count()');
        }
        return $items;
    }

    protected function _cacheKeyBase() {
        return array($this->_collection, $this->_criteria, $this->_projection);
    }

    public function getStalenessChance() {
        return 0.01;
    }
}
