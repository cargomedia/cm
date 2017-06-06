<?php

class CM_Model_Splittest extends CM_Model_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string             $name
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct($name, CM_Service_Manager $serviceManager = null) {
        $this->_construct(['name' => $name]);
        if (null === $serviceManager) {
            $serviceManager = CM_Service_Manager::getInstance();
        }
        $this->setServiceManager($serviceManager);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_getIdKey('name');
    }

    public function getId() {
        return (int) $this->_get('id');
    }

    /**
     * @return int
     */
    public function getCreated() {
        return (int) $this->_get('createStamp');
    }

    /**
     * @param int $timestamp
     * @return CM_Model_Splittest
     */
    public function setCreated($timestamp) {
        $timestamp = (int) $timestamp;
        CM_Db_Db::update('cm_splittest', ['createStamp' => $timestamp], ['id' => $this->getId()]);
        return $this->_change();
    }

    /**
     * @return bool
     */
    public function getOptimized() {
        return (bool) $this->_get('optimized');
    }

    /**
     * @param bool $optimized
     * @return CM_Model_Splittest
     */
    public function setOptimized($optimized) {
        $optimized = (bool) $optimized;
        CM_Db_Db::update('cm_splittest', ['optimized' => $optimized], ['id' => $this->getId()]);
        return $this->_change();
    }

    /**
     * @return CM_Model_SplittestVariation[]
     */
    public function getVariationListSorted() {
        $variationList = $this->getVariations()->getItems();
        usort($variationList, function (CM_Model_SplittestVariation $variationA, CM_Model_SplittestVariation $variationB) {
            return $variationA->getConversionRate() > $variationB->getConversionRate() ? -1 : 1;
        });
        return $variationList;
    }

    /**
     * @return CM_Paging_SplittestVariation_Splittest
     */
    public function getVariations() {
        return new CM_Paging_SplittestVariation_Splittest($this);
    }

    /**
     * @return CM_Paging_SplittestVariation_SplittestEnabled
     */
    public function getVariationsEnabled() {
        return new CM_Paging_SplittestVariation_SplittestEnabled($this);
    }

    /**
     * @return int
     */
    public function getVariationFixtureCreatedMin() {
        return (int) CM_Db_Db::exec(
            'SELECT MIN(`createStamp`) FROM `cm_splittestVariation_fixture` WHERE `splittestId` = ?', [$this->getId()])->fetchColumn();
    }

    /**
     * @throws CM_Exception
     * @return CM_Model_SplittestVariation
     */
    public function getVariationBest() {
        $variationBest = null;
        $variationBestRate = 0;
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->getVariations() as $variation) {
            $variationRate = $variation->getConversionRate();
            if (null === $variationBest || $variationRate > $variationBestRate) {
                $variationBest = $variation;
                $variationBestRate = $variationRate;
            }
        }
        if (!$variationBest) {
            throw new CM_Exception('Splittest has no variations', null, ['splitTestId' => $this->getId()]);
        }
        return $variationBest;
    }

    public function flush() {
        CM_Db_Db::delete('cm_splittestVariation_fixture', ['splittestId' => $this->getId()]);
        $this->setCreated(time());
    }

    /**
     * @param int $id
     * @return CM_Model_Splittest
     * @throws CM_Exception_Nonexistent
     */
    public static function findId($id) {
        $id = (int) $id;
        $name = CM_Db_Db::select('cm_splittest', 'name', ['id' => $id])->fetchColumn();
        if (false === $name) {
            throw new CM_Exception_Nonexistent('Cannot find splittest by id', null, ['id' => $id]);
        }
        return new static($name);
    }

    protected function _loadData() {
        $data = CM_Db_Db::select('cm_splittest', '*', ['name' => $this->getName()])->fetch();
        if ($data) {
            $data['variations'] = CM_Db_Db::select('cm_splittestVariation',
                ['id', 'name'], ['splittestId' => $data['id']])->fetchAllTree();
        }
        return $data;
    }

    protected static function _createStatic(array $data) {
        $name = (string) $data['name'];
        $variations = array_unique($data['variations']);
        if (empty($variations)) {
            throw new CM_Exception('Cannot create splittest without variations');
        }
        $optimized = !empty($data['optimized']);

        $id = CM_Db_Db::insert('cm_splittest', ['name' => $name, 'optimized' => $optimized, 'createStamp' => time()]);
        try {
            foreach ($variations as $variation) {
                CM_Db_Db::insert('cm_splittestVariation', ['splittestId' => $id, 'name' => $variation]);
            }
        } catch (CM_Exception $e) {
            CM_Db_Db::delete('cm_splittest', ['id' => $id]);
            CM_Db_Db::delete('cm_splittestVariation', ['splittestId' => $id]);
            throw $e;
        }
        return new static($name);
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_splittestVariation', ['splittestId' => $this->getId()]);
        CM_Db_Db::delete('cm_splittestVariation_fixture', ['splittestId' => $this->getId()]);
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_splittest', ['id' => $this->getId()]);
    }

    protected function _getContainingCacheables() {
        $containingCacheables = parent::_getContainingCacheables();
        $containingCacheables[] = new CM_Paging_Splittest_All();
        return $containingCacheables;
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param float|null           $weight
     * @throws CM_Exception_Invalid
     */
    protected function _setConversion(CM_Splittest_Fixture $fixture, $weight = null) {
        $fixtureId = $fixture->getId();
        $columnIdQuoted = CM_Db_Db::getClient()->quoteIdentifier($fixture->getColumnId());
        if (null === $weight) {
            CM_Db_Db::exec('UPDATE `cm_splittestVariation_fixture`
                SET `conversionStamp` = COALESCE(`conversionStamp`, ?), `conversionWeight` = 1
                WHERE `splittestId` = ? AND ' . $columnIdQuoted . ' = ?',
                [time(), $this->getId(), $fixtureId]);
        } else {
            $weight = (float) $weight;
            CM_Db_Db::exec('UPDATE `cm_splittestVariation_fixture`
                SET `conversionStamp` = COALESCE(`conversionStamp`, ?), `conversionWeight` = `conversionWeight` + ?
                WHERE `splittestId` = ? AND ' . $columnIdQuoted . ' = ?',
                [time(), $weight, $this->getId(), $fixtureId]);
        }
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param string               $variationName
     * @return bool
     */
    protected function _isVariationFixture(CM_Splittest_Fixture $fixture, $variationName) {
        return ($variationName == $this->_getVariationFixture($fixture));
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param bool|null            $updateCache
     * @return string|null
     */
    protected function _findVariationNameFixture(CM_Splittest_Fixture $fixture, $updateCache = null) {
        $updateCache = (bool) $updateCache;
        $variationDataList = self::getVariationDataListFixture($fixture, $updateCache);
        if ($updateCache) {
            $this->_change();
        }
        $splittestId = $this->getId();
        if (!isset($variationDataList[$splittestId]) || (int) $variationDataList[$splittestId]['flushStamp'] !== $this->getCreated()) {
            return null;
        }
        return $variationDataList[$splittestId]['variation'];
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @throws CM_Db_Exception
     * @throws CM_Exception_Invalid
     * @return string
     */
    protected function _getVariationFixture(CM_Splittest_Fixture $fixture) {
        $variationName = $this->_findVariationNameFixture($fixture);
        if (null === $variationName) {
            $variation = $this->_getVariationRandom();
            $variationName = $variation->getName();
            try {
                $columnId = $fixture->getColumnId();
                $fixtureId = $fixture->getId();
                CM_Db_Db::insert('cm_splittestVariation_fixture',
                    ['splittestId' => $this->getId(), $columnId => $fixtureId, 'variationId' => $variation->getId(), 'createStamp' => time()]);
                $variationDataList = self::getVariationDataListFixture($fixture);
                $variationDataList[$this->getId()] = [
                    'variation'  => $variationName,
                    'splittest'  => $this->getName(),
                    'flushStamp' => $this->getCreated(),
                ];
                $cacheKey = self::_getCacheKeyFixture($fixture);
                $cache = CM_Cache_Shared::getInstance();
                $cache->set($cacheKey, $variationDataList);
                $this->getServiceManager()->getTrackings()->trackSplittest($fixture, $variation);
            } catch (CM_Db_Exception $exception) {
                $variationName = $this->_findVariationNameFixture($fixture, true);
                if (null === $variationName) {
                    throw $exception;
                }
            }
        }
        return $variationName;
    }

    /**
     * @param CM_Splittest_Fixture        $fixture
     * @param CM_Model_SplittestVariation $variation
     */
    protected function _setVariationFixture(CM_Splittest_Fixture $fixture, CM_Model_SplittestVariation $variation) {
        $variationName = $variation->getName();
        $variationNameOld = $this->_findVariationNameFixture($fixture);
        if ($variationName === $variationNameOld) {
            return;
        }
        $columnId = $fixture->getColumnId();
        $fixtureId = $fixture->getId();
        if (null === $variationNameOld) {
            CM_Db_Db::insert('cm_splittestVariation_fixture',
                ['splittestId' => $this->getId(), $columnId => $fixtureId, 'variationId' => $variation->getId(), 'createStamp' => time()]);
        } else {
            CM_Db_Db::update('cm_splittestVariation_fixture',
                ['variationId' => $variation->getId()], ['splittestId' => $this->getId(), $columnId => $fixtureId]);
        }
        $variationDataList = self::getVariationDataListFixture($fixture);
        $variationDataList[$this->getId()] = [
            'variation'  => $variationName,
            'splittest'  => $this->getName(),
            'flushStamp' => $this->getCreated(),
        ];
        $cacheKey = self::_getCacheKeyFixture($fixture);
        $cache = CM_Cache_Shared::getInstance();
        $cache->set($cacheKey, $variationDataList);
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Model_SplittestVariation
     */
    protected function _getVariationRandom() {
        if ($this->getOptimized()) {
            $variation = $this->_getVariationWithUpperConfidenceBoundPolicy();
        } else {
            $variation = $this->_getVariationWithFixedWeightPolicy();
        }
        if (!$variation) {
            throw new CM_Exception_Invalid('Splittest has no enabled variations.', null, ['splitTestId' => $this->getId(),]);
        }
        return $variation;
    }

    /**
     * @return CM_Model_SplittestVariation|null
     */
    protected function _getVariationWithFixedWeightPolicy() {
        $variationList = [];
        $variationWeightList = [];
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->getVariationsEnabled()->getItems() as $variation) {
            $variationList[] = $variation;
            $variationWeightList[] = $variation->getFrequency();
        }
        if (empty($variationList)) {
            return null;
        }
        $weightedRandom = new CM_WeightedRandom($variationList, $variationWeightList);
        return $weightedRandom->lookup();
    }

    /**
     * @see Section 4 of http://homes.di.unimi.it/~cesabian/Pubblicazioni/ml-02.pdf
     * @return CM_Model_SplittestVariation|null
     */
    protected function _getVariationWithUpperConfidenceBoundPolicy() {
        $variationList = $this->getVariationsEnabled();
        $variationListUninitialized = [];
        /** @var CM_Model_SplittestVariation $variationEnabled */
        foreach ($variationList as $variationEnabled) {
            if (0. === $variationEnabled->getStandardDeviation()) {
                $variationListUninitialized[$variationEnabled->getFixtureCount()] = $variationEnabled;
            }
        }
        if (!empty($variationListUninitialized)) {
            ksort($variationListUninitialized);
            return reset($variationListUninitialized);
        }
        $variation = null;
        $upperConfidenceBoundMax = null;
        foreach ($variationList as $variationEnabled) {
            $upperConfidenceBound = $variationEnabled->getUpperConfidenceBound();
            if ((null === $upperConfidenceBoundMax) || ($upperConfidenceBound > $upperConfidenceBoundMax)) {
                $variation = $variationEnabled;
                $upperConfidenceBoundMax = $upperConfidenceBound;
            }
        }
        return $variation;
    }

    /**
     * @param string    $name
     * @param string[]  $variations
     * @param bool|null $optimized
     * @return static
     */
    public static function create($name, array $variations, $optimized = null) {
        $name = (string) $name;
        $optimized = (bool) $optimized;
        return static::createStatic(['name' => $name, 'variations' => $variations, 'optimized' => $optimized]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function exists($name) {
        $paging = new CM_Paging_Splittest_All();
        return $paging->contains($name);
    }

    /**
     * @param string $name
     * @return static|null
     */
    public static function find($name) {
        if (!self::exists($name)) {
            return null;
        }
        $className = get_called_class();
        return new $className($name);
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param bool|null            $updateCache
     * @return array
     */
    public static function getVariationDataListFixture(CM_Splittest_Fixture $fixture, $updateCache = null) {
        $columnId = $fixture->getColumnId();
        $columnIdQuoted = CM_Db_Db::getClient()->quoteIdentifier($columnId);
        $fixtureId = $fixture->getId();
        $updateCache = (bool) $updateCache;

        $cacheKey = self::_getCacheKeyFixture($fixture);
        $cache = CM_Cache_Shared::getInstance();
        if ($updateCache || (($variationListFixture = $cache->get($cacheKey)) === false)) {
            $variationListFixture = CM_Db_Db::exec('
				SELECT `variation`.`splittestId`, `variation`.`name` AS `variation`, `splittest`.`name` AS `splittest`, `splittest`.`createStamp` AS `flushStamp`
					FROM `cm_splittestVariation_fixture` `fixture`
					JOIN `cm_splittestVariation` `variation` ON(`variation`.`id` = `fixture`.`variationId`)
					JOIN `cm_splittest` `splittest` ON(`splittest`.`id` = `fixture`.`splittestId`)
					WHERE `fixture`.' . $columnIdQuoted . ' = ?', [$fixtureId])->fetchAllTree();
            $cache->set($cacheKey, $variationListFixture);
        }
        return $variationListFixture;
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @return string
     */
    protected static function _getCacheKeyFixture(CM_Splittest_Fixture $fixture) {
        return CM_CacheConst::Splittest_VariationFixtures . '_id:' . $fixture->getId() . '_type:' . $fixture->getFixtureType();
    }
}
