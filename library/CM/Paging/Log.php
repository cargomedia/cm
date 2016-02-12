<?php

class CM_Paging_Log extends CM_Paging_Abstract implements CM_Typed {

    const COLLECTION_NAME = 'cm_log';

    /** @var int */
    protected $_level;

    /**
     * @param int     $level
     * @param boolean $aggregate
     * @param int     $ageMax
     */
    public function __construct($level, $aggregate = false, $ageMax = null) {
        $level = (int) $level;

        $this->_level = $level;
        $criteria = ['level' => $this->_level];
        if ($ageMax) {
            $criteria['createdAt'] = ['$gt' => new MongoDate(time() - (int) $ageMax)];
        }
        if (true === $aggregate) {
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, null, null, [
                ['$match' => $criteria],
                ['$group' => [
                    '_id'   => ['message' => '$message', 'exception' => '$exception'],
                    'count' => ['$sum' => 1]]
                ],
                ['$sort' => ['count' => -1]],
                ['$project' => [
                    'message'   => '$_id.message',
                    'exception' => '$_id.exception',
                    'count'     => '$count',
                    '_id'       => false]
                ]
            ]);
        } else {
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, $criteria, null, null, ['_id' => -1]);
        }
        parent::__construct($source);
    }

    public function flush() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->remove(self::COLLECTION_NAME, ['level' => $this->_level]);
        $this->_change();
    }

    public function cleanUp() {
        $this->_deleteOlderThan(7 * 86400);
    }

    /**
     * @param int $age
     */
    protected function _deleteOlderThan($age) {
        $age = (int) $age;
        $deleteOlderThan = time() - $age;

        $criteria = [
            'level'     => $this->_level,
            'createdAt' => ['$lt' => new MongoDate($deleteOlderThan)]
        ];

        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->remove(self::COLLECTION_NAME, $criteria);
        $this->_change();
    }

    /**
     * @param int       $level
     * @param bool|null $aggregate
     * @param int|null  $ageMax
     * @return CM_Paging_Log
     */
    final public static function factory($level, $aggregate = null, $ageMax = null) {
        return new self($level, $aggregate, $ageMax);
    }
}
