<?php

abstract class CM_Paging_Log_Abstract extends CM_Paging_Abstract implements CM_Typed {

    const COLLECTION_NAME = 'cm_log';

    /**
     * @param boolean $aggregate
     * @param int     $ageMax
     */
    public function __construct($aggregate = false, $ageMax = null) {
        $criteria = ['type' => $this->getType()];
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
        $mongoDb->remove(self::COLLECTION_NAME, ['type' => $this->getType()]);
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
            'type'      => $this->getType(),
            'createdAt' => ['$lt' => new MongoDate($deleteOlderThan)]
        ];

        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->remove(self::COLLECTION_NAME, $criteria);
        $this->_change();
    }

    /**
     * @param int       $type
     * @param bool|null $aggregate
     * @param int|null  $ageMax
     * @return CM_Paging_Log_Abstract
     */
    final public static function factory($type, $aggregate = null, $ageMax = null) {
        $className = self::_getClassName($type);
        return new $className($aggregate, $ageMax);
    }
}
