<?php

class CM_Paging_Log extends CM_Paging_Abstract implements CM_Typed {

    const COLLECTION_NAME = 'cm_log';

    /** @var int */
    protected $_level;

    /** @var int|null */
    protected $_type;

    /**
     * @param int          $level
     * @param boolean|null $aggregate
     * @param int          $ageMax
     * @param int|null     $type
     * @throws CM_Exception_Invalid
     */
    public function __construct($level, $aggregate = null, $ageMax = null, $type = null) {
        $level = (int) $level;
        if (null !== $ageMax) {
            $ageMax = (int) $ageMax;
        }

        if (!CM_Log_Logger::hasLevel($level)) {
            throw new CM_Exception_Invalid('Log level `' . $level . '` does not exist.');
        }

        if (null !== $type) {
            $type = (int) $type;
            $childrenTypeList = \Functional\map(CM_Paging_Log::getClassChildren(), function ($className) {
                /** @type CM_Class_Abstract $className */
                return $className::getTypeStatic();
            });
            if (!in_array($type, $childrenTypeList)) {
                throw new CM_Exception_Invalid('Type is not a children of CM_Paging_Log.');
            }
        }
        $this->_level = $level;
        $this->_type = $type;

        $criteria = [
            'level' => $this->_level,
        ];
        $criteria = array_merge($criteria, self::addTypeCriteria($this->_type));

        if ($ageMax) {
            $criteria['createdAt'] = ['$gt' => new MongoDate(time() - $ageMax)];
        }

        if (true === $aggregate) {
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, null, null, [
                ['$match' => $criteria],
                ['$group' => [
                    '_id'   => ['level' => '$level', 'message' => '$message', 'exception' => '$exception'],
                    'count' => ['$sum' => 1]]
                ],
                ['$sort' => ['count' => -1]],
                ['$project' => [
                    'level'     => '$_id.level',
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

        $criteria = ['level' => $this->_level];
        $criteria = array_merge($criteria, self::addTypeCriteria($this->_type));

        $mongoDb->remove(self::COLLECTION_NAME, $criteria);
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
            'createdAt' => ['$lt' => new MongoDate($deleteOlderThan)],
        ];
        $criteria = array_merge($criteria, self::addTypeCriteria($this->_type));

        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->remove(self::COLLECTION_NAME, $criteria);
        $this->_change();
    }

    /**
     * @param int|null $type
     * @return array
     */
    public static function addTypeCriteria($type = null) {
        $criteria = [];
        if (null !== $type) {
            $criteria['context.extra.type'] = (int) $type;
        }
        return $criteria;
    }
}
