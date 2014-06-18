<?php

/**
 * Class CM_Model_Splittest
 * @method bool isVariationFixture($fixtureSource, $variationName)
 * @method setConversion($fixtureSource, $weight)
 */
class CM_Model_Splittest extends CM_Model_Abstract {

    /** @var bool */
    private $_withoutPersistence;

    /** @var array|null */
    private $_variationWeightList;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->_construct(array('name' => $name));
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
            'SELECT MIN(`createStamp`) FROM `cm_splittestVariation_fixture` WHERE `splittestId` = ?', array($this->getId()))->fetchColumn();
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
            throw new CM_Exception('Splittest `' . $this->getId() . '` has no variations');
        }
        return $variationBest;
    }

    /**
     * @param array $variationWeightList
     * @throws CM_Exception_Invalid
     */
    public function setVariationWeightList(array $variationWeightList) {
        if ($this->_withoutPersistence) {
            return;
        }
        if (empty($variationWeightList)) {
            throw new CM_Exception_Invalid('Empty variation weight list');
        }
        $variationList = $this->getVariations();
        $this->_variationWeightList = array();
        foreach ($variationWeightList as $variationName => $variationWeight) {
            $variationName = (string) $variationName;
            $variationWeight = (float) $variationWeight;
            $variation = $variationList->findByName($variationName);
            if (!$variation) {
                throw new CM_Exception_Invalid('There is no variation `' . $variationName . '` in split test `' . $this->getName() . '`');
            }
            if ($variationWeight < 0) {
                throw new CM_Exception_Invalid('Split test variation weight `' . $variationWeight . '` should be positive');
            }
            if ($variation->getEnabled() && ($variationWeight > 0)) {
                $this->_variationWeightList[$variationName] = $variationWeight;
            }
        }
        if (empty($this->_variationWeightList)) {
            throw new CM_Exception_Invalid('At least one enabled split test variation should have a positive weight');
        }
    }

    public function flush() {
        CM_Db_Db::delete('cm_splittestVariation_fixture', array('splittestId' => $this->getId()));
    }

    /**
     * @param int $id
     * @return CM_Model_Splittest
     * @throws CM_Exception_Nonexistent
     */
    public static function findId($id) {
        $id = (int) $id;
        $name = CM_Db_Db::select('cm_splittest', 'name', array('id' => $id))->fetchColumn();
        if (false === $name) {
            throw new CM_Exception_Nonexistent('Cannot find splittest with id `' . $id . '`');
        }
        return new static($name);
    }

    protected function _loadData() {
        $data = CM_Db_Db::select('cm_splittest', '*', array('name' => $this->getName()))->fetch();
        if ($data) {
            $data['variations'] = CM_Db_Db::select('cm_splittestVariation',
                array('id', 'name'), array('splittestId' => $data['id']))->fetchAllTree();
        }
        return $data;
    }

    protected static function _createStatic(array $data) {
        $name = (string) $data['name'];
        $variations = array_unique($data['variations']);
        if (empty($variations)) {
            throw new CM_Exception('Cannot create splittest without variations');
        }

        $id = CM_Db_Db::insert('cm_splittest', array('name' => $name, 'createStamp' => time()));
        try {
            foreach ($variations as $variation) {
                CM_Db_Db::insert('cm_splittestVariation', array('splittestId' => $id, 'name' => $variation));
            }
        } catch (CM_Exception $e) {
            CM_Db_Db::delete('cm_splittest', array('id' => $id));
            CM_Db_Db::delete('cm_splittestVariation', array('splittestId' => $id));
            throw $e;
        }
        return new static($name);
    }

    protected function _onDeleteBefore() {
        CM_Db_Db::delete('cm_splittestVariation', array('splittestId' => $this->getId()));
        CM_Db_Db::delete('cm_splittestVariation_fixture', array('splittestId' => $this->getId()));
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_splittest', array('id' => $this->getId()));
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param float|null           $weight
     * @throws CM_Exception_Invalid
     */
    protected function _setConversion(CM_Splittest_Fixture $fixture, $weight = null) {
        if (null === $weight) {
            $weight = 1;
        }
        if ($weight <= 0) {
            throw new CM_Exception_Invalid('Weight must be positive or null, `' . $weight . '` given');
        }
        $weight = (float) $weight;
        $fixtureId = $fixture->getId();
        $columnId = $fixture->getColumnId();

        CM_Db_Db::update('cm_splittestVariation_fixture',
            array('conversionStamp' => time(), 'conversionWeight' => $weight),
            array('splittestId' => $this->getId(), $columnId => $fixtureId));
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
     * @throws CM_Exception_Invalid
     * @return string
     */
    protected function _getVariationFixture(CM_Splittest_Fixture $fixture) {
        $columnId = $fixture->getColumnId();
        $fixtureId = $fixture->getId();

        $cacheKey = CM_CacheConst::Splittest_VariationFixtures . '_id:' . $fixture->getId() . '_type:' . $fixture->getFixtureType();
        $cacheWrite = false;
        $cache = CM_Cache_Local::getInstance();
        if (($variationFixtureList = $cache->get($cacheKey)) === false) {
            $variationFixtureList = CM_Db_Db::exec('
				SELECT `variation`.`splittestId`, `variation`.`name`
					FROM `cm_splittestVariation_fixture` `fixture`
					JOIN `cm_splittestVariation` `variation` ON(`variation`.`id` = `fixture`.`variationId`)
					WHERE `fixture`.`' . $columnId . '` = ?', array($fixtureId))->fetchAllTree();
            $cacheWrite = true;
        }

        if (!array_key_exists($this->getId(), $variationFixtureList)) {
            $variation = $this->_getVariationRandom();
            CM_Db_Db::replace('cm_splittestVariation_fixture',
                array('splittestId' => $this->getId(), $columnId => $fixtureId, 'variationId' => $variation->getId(), 'createStamp' => time()));
            $variationFixtureList[$this->getId()] = $variation->getName();
            $cacheWrite = true;
        }

        if ($cacheWrite) {
            $cache->set($cacheKey, $variationFixtureList);
        }

        return $variationFixtureList[$this->getId()];
    }

    /**
     * @throws CM_Exception_Invalid
     * @return CM_Model_SplittestVariation
     */
    protected function _getVariationRandom() {
        if (!isset($this->_variationWeightList)) {
            $variation = $this->getVariationsEnabled()->getItemRand();
        } else {
            $variationList = array();
            $variationWeightList = array();
            /** @var CM_Model_SplittestVariation $variation */
            foreach ($this->getVariationsEnabled()->getItems() as $variation) {
                $variationName = $variation->getName();
                if (isset($this->_variationWeightList[$variationName])) {
                    $variationList[] = $variation;
                    $variationWeightList[] = $this->_variationWeightList[$variationName];
                }
            }
            if (empty($variationList)) {
                $variation = null;
            } else {
                $weightedRandom = new CM_WeightedRandom($variationList, $variationWeightList);
                $variation = $weightedRandom->lookup();
            }
        }
        if (!$variation) {
            throw new CM_Exception_Invalid('Splittest `' . $this->getId() . '` has no enabled variations.');
        }
        return $variation;
    }

    /**
     * @param string   $name
     * @param string[] $variations
     * @return static
     */
    public static function create($name, array $variations = null) {
        return static::createStatic(['name' => (string) $name, 'variations' => (array) $variations]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function exists($name) {
        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::CM_Model_Splittest_exists . '_name' . $name;
        if (false === ($exists = $cache->get($cacheKey))) {
            $exists = CM_Db_Db::count('cm_splittest', ['name' => $name]);
            $cache->set($cacheKey, $exists);
        }
        return $exists;
    }

    /**
     * @param string     $splittestName
     * @param mixed      $fixtureSource
     * @param float|null $weight
     */
    protected static function _setConversionStatic($splittestName, $fixtureSource, $weight = null) {
        $splittest = static::_getSplittest($splittestName);
        if ($splittest) {
            $splittest->setConversion($fixtureSource, $weight);
        }
    }

    /**
     * @param string $splittestName
     * @param mixed  $fixture
     * @param string $variationName
     * @return bool
     */
    protected static function _isVariationFixtureStatic($splittestName, $fixture, $variationName) {
        $splittest = static::_getSplittest($splittestName);
        if (!$splittest) {
            return false;
        }
        return $splittest->isVariationFixture($fixture, $variationName);
    }

    /**
     * @param $name
     * @return CM_Model_Splittest|null
     */
    protected static function _getSplittest($name) {
        if (!self::exists($name)) {
            return null;
        }
        $className = get_called_class();
        return new $className($name);
    }
}
