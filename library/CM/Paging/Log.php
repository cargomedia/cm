<?php

class CM_Paging_Log extends CM_Paging_Abstract implements CM_Typed {

    const COLLECTION_NAME = 'cm_log';

    /** @var array|null */
    protected $_filterLevelList;

    /** @var int|boolean|null */
    protected $_filterType;

    /** @var int|null */
    protected $_ageMax;

    /**
     * @param array            $filterLevelList
     * @param int|boolean|null $filterType
     * @param boolean|null     $aggregate
     * @param int|null         $ageMax
     * @throws CM_Exception_Invalid
     */
    public function __construct(array $filterLevelList = null, $filterType = null, $aggregate = null, $ageMax = null) {
        if (null !== $filterLevelList) {
            foreach ($filterLevelList as $level) {
                $level = (int) $level;
                if (!CM_Log_Logger::hasLevel($level)) {
                    throw new CM_Exception_Invalid('Log level does not exist.', null, ['level' => $level]);
                }
            }
        }

        if (null !== $filterType && false !== $filterType && !self::isValidType((int) $filterType)) {
            throw new CM_Exception_Invalid('Type is not a children of CM_Paging_Log.');
        }

        if (null !== $ageMax) {
            $ageMax = (int) $ageMax;
        }

        $this->_filterLevelList = $filterLevelList;
        $this->_filterType = $filterType;
        $this->_ageMax = $ageMax;

        $criteria = $this->_getCriteria();

        if (true === $aggregate) {
            $aggregate = [
                ['$match' => $criteria],
                ['$group' => [
                    '_id'       => [
                        'level'             => '$level',
                        'message'           => '$message',
                        'exception_message' => '$context.exception.message',
                        'exception_class'   => '$context.exception.class',
                        'exception_line'    => '$context.exception.line',
                        'exception_file'    => '$context.exception.file',
                        //stack trace can be different only by 1 line
                    ],
                    'count'     => ['$sum' => 1],
                    'createdAt' => ['$max' => '$createdAt'],
                    'exception' => ['$last' => '$context.exception']],
                ],
                ['$sort' => ['count' => -1]],
                ['$project' => [
                    'level'     => '$_id.level',
                    'message'   => '$_id.message',
                    'exception' => '$exception',
                    'count'     => '$count',
                    'createdAt' => '$createdAt',
                    '_id'       => false],
                ],
            ];
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, null, null, $aggregate);
        } else {
            $sorting = empty($criteria) ? ['_id' => -1] : ['createdAt' => -1];
            $source = new CM_PagingSource_MongoDb(self::COLLECTION_NAME, $criteria, null, null, $sorting);
        }

        parent::__construct($source);
    }

    public function flush() {
        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $criteria = $this->_getCriteria();
        $mongoDb->deleteMany(self::COLLECTION_NAME, $criteria, ['socketTimeoutMS' => 50000]);
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

        $criteria = $this->_getCriteria();
        $criteria['createdAt'] = ['$lt' => new \MongoDB\BSON\UTCDateTime($deleteOlderThan * 1000)];

        $mongoDb = CM_Service_Manager::getInstance()->getMongoDb();
        $mongoDb->deleteMany(self::COLLECTION_NAME, $criteria, ['socketTimeoutMS' => 50000]);
        $this->_change();
    }

    /**
     * @return array
     */
    protected function _getCriteria() {
        $criteria = [];

        if (false === $this->_filterType) {
            $criteria['context.extra.type'] = CM_Log_Handler_MongoDb::DEFAULT_TYPE;
        } elseif (null !== $this->_filterType) {
            $criteria['context.extra.type'] = (int) $this->_filterType;
        }

        if (null !== $this->_filterLevelList) {
            $criteria['level'] = ['$in' => $this->_filterLevelList];
        }

        if (null !== $this->_ageMax) {
            $criteria['createdAt'] = ['$gt' => new \MongoDB\BSON\UTCDateTime((time() - $this->_ageMax) * 1000)];
        }

        return $criteria;
    }

    protected function _processItem($itemRaw) {
        if (isset($itemRaw['context']['exception'])) {
            $itemRaw['exception'] = $itemRaw['context']['exception'];
            unset($itemRaw['context']['exception']);
        }
        return $itemRaw;
    }

    /**
     * @param int $type
     * @return bool
     */
    public static function isValidType($type) {
        $type = (int) $type;
        $childrenTypeList = \Functional\map(CM_Paging_Log::getClassChildren(), function ($className) {
            /** @type CM_Class_Abstract $className */
            return $className::getTypeStatic();
        });
        return in_array($type, $childrenTypeList);
    }
}
