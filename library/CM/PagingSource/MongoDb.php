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

    /** @var array|null  */
    private $_sort;

    /**
     * @param string     $collection
     * @param array|null $criteria
     * @param array|null $projection  http://docs.mongodb.org/manual/reference/method/db.collection.find/#projections
     * @param array|null $aggregation http://docs.mongodb.org/manual/core/aggregation-pipeline/
     * @param array|null $sort
     *
     * When using aggregation, $criteria and $projection, if defined, automatically
     * function as `$match` and `$project` operator respectively at the front of the pipeline
     */
    public function __construct($collection, array $criteria = null, array $projection = null, array $aggregation = null, array $sort = null) {
        $this->_collection = (string) $collection;
        $this->_criteria = (array) $criteria;
        $this->_projection = (array) $projection;
        $this->_aggregation = $aggregation;
        $this->_sort = $sort;
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
        }
        return $count;
    }

    /**
     * @param int|null $offset
     * @param int|null $count
     * @return array
     */
    public function getItems($offset = null, $count = null) {
        $cacheKey = array('items', $offset, $count, $this->_sort);
        if (($items = $this->_cacheGet($cacheKey)) === false) {
            $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
            $aggregation = null;
            if ($this->_aggregation) {
                $aggregation = $this->_aggregation;
                if (null !== $this->_sort) {
                    array_push($aggregation, ['$sort' => $this->_sort]);
                }
                if (null !== $offset) {
                    array_push($aggregation, ['$skip' => $offset]);
                }
                if (null !== $count) {
                    array_push($aggregation, ['$limit' => $count]);
                }
            }
            $cursor = $mongoDb->find($this->_collection, $this->_criteria, $this->_projection, $aggregation);
            if (null === $this->_aggregation) {
                /** @var MongoCursor $cursor */
                if (null !== $this->_sort) {
                    $cursor->sort($this->_sort);
                }
                if (null !== $offset) {
                    $cursor->skip($offset);
                }
                if (null !== $count) {
                    $cursor->limit($count);
                }
            }
            $items = array();
            foreach ($cursor as $item) {
                $items[] = $item;
            }
            $this->_cacheSet($cacheKey, $items);
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
