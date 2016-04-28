<?php

class CM_Paging_Log extends CM_Paging_Abstract implements CM_Typed {

    const COLLECTION_NAME = 'cm_log';

    /** @var array */
    protected $_levelList;

    /** @var int|null */
    protected $_type;

    /**
     * @param array        $levelList
     * @param boolean|null $aggregate
     * @param int          $ageMax
     * @param int|null     $type
     * @throws CM_Exception_Invalid
     */
    public function __construct(array $levelList, $aggregate = null, $ageMax = null, $type = null) {
        if (empty($levelList)) {
            throw new CM_Exception_Invalid('Log level list is empty.');
        }
        foreach ($levelList as $level) {
            $level = (int) $level;
            if (!CM_Log_Logger::hasLevel($level)) {
                throw new CM_Exception_Invalid('Log level `' . $level . '` does not exist.');
            }
        }

        if (null !== $ageMax) {
            $ageMax = (int) $ageMax;
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
        $this->_levelList = $levelList;
        $this->_type = $type;

        $criteria = [
            'level' => ['$in' => $this->_levelList],
        ];
        $criteria = array_merge($criteria, self::_addTypeCriteria($this->_type));

        if ($ageMax) {
            $criteria['createdAt'] = ['$gt' => new MongoDate(time() - $ageMax)];
        }

        if (true === $aggregate) {
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, null, null, [
                ['$match' => $criteria],
                ['$group' => [
                    '_id'   => [
                        'level' => '$level',
                        'message' => '$message',
                        'exception_message' => '$context.exception.message',
                        'exception_class' => '$context.exception.class',
                        'exception_line' => '$context.exception.line',
                        'exception_file' => '$context.exception.file',
                        //stack trace can be different only by 1 line
                    ],
                    'count' => ['$sum' => 1],
                    'exception' => ['$last' => '$context.exception' ]],
                ],
                ['$sort' => ['count' => -1]],
                ['$project' => [
                    'level'             => '$_id.level',
                    'message'           => '$_id.message',
                    'context.exception' => '$exception',
                    'count'             => '$count',
                    '_id'               => false]
                ]
            ]);
        } else {
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, $criteria, null, null, ['_id' => -1]);
        }
        parent::__construct($source);
    }

    public function flush() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();

        $criteria = ['level' => ['$in' => $this->_levelList]];
        $criteria = array_merge($criteria, self::_addTypeCriteria($this->_type, true));

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
            'level'     => ['$in' => $this->_levelList],
            'createdAt' => ['$lt' => new MongoDate($deleteOlderThan)],
        ];
        $criteria = array_merge($criteria, self::_addTypeCriteria($this->_type, true));

        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->remove(self::COLLECTION_NAME, $criteria);
        $this->_change();
    }

    /**
     * @param int|null  $type
     * @param bool|null $excludeTyped
     * @return array
     */
    protected static function _addTypeCriteria($type = null, $excludeTyped = null) {
        $criteria = [];
        if (null !== $type) {
            $criteria['context.extra.type'] = (int) $type;
        } elseif (true === $excludeTyped) {
            $criteria['context.extra.type'] = ['$exists' => false];
        }
        return $criteria;
    }
}
