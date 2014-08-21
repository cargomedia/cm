<?php

class CM_PagingSource_MongoDb extends CM_PagingSource_Abstract {

    /** @var string */
    private $_collection;

    /** @var array */
    private $_criteria;

    /** @var array */
    private $_projection;

    /** @var array */
    private $_aggregation;

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param array|null $projection  http://docs.mongodb.org/manual/reference/method/db.collection.find/#projections
     * @param array|null $aggregation http://docs.mongodb.org/manual/core/aggregation-pipeline/
     *
     * When using aggregation, $criteria and $projection, if defined, automatically
     * function as `$match` and `$project` operator respectively at the front of the pipeline
     */
    public function __construct($collection, array $criteria = null, array $projection = null, array $aggregation = null) {
        $this->_collection = (string) $collection;
        $this->_criteria = (array) $criteria;
        $this->_projection = (array) $projection;
        $this->_aggregation = $aggregation;
    }

    /**
     * @param int|null $offset
     * @param int|null $count
     * @return int
     */
    public function getCount($offset = null, $count = null) {
        $cacheKey = array('count');
        if (($count = $this->_cacheGet($cacheKey)) === false) {
            $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
            $count = $mongoDb->count($this->_collection, $this->_criteria, $this->_aggregation, $count, $offset);
            $this->_cacheSet($cacheKey, $count);
        } else {
            CM_Debug::getInstance()->incStats('mongoCacheHit', 'count()');
        }
        return $count;
    }

    /**
     * @param int|null $offset
     * @param int|null $count
     * @return array
     */
    public function getItems($offset = null, $count = null) {
        $cacheKey = array('items', $offset, $count);
        if (($items = $this->_cacheGet($cacheKey)) === false) {
            $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
            $cursor = $mongoDb->find($this->_collection, $this->_criteria, $this->_projection, $this->_aggregation);

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
            CM_Debug::getInstance()->incStats('mongoCacheHit', 'getItems()');
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
