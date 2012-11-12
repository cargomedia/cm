<?php

class CM_Model_Splittest extends CM_Model_Abstract {
	CONST TYPE = 16;

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->_construct(array('name' => (string) $name));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_getId('name');
	}

	/**
	 * @return int
	 */
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
		return (int) CM_Mysql::exec(
			'SELECT MIN(`createStamp`) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE WHERE `splittestId` = ' . $this->getId())->fetchOne();
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

	public function flush() {
		CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('splittestId' => $this->getId()));
	}

	protected function _loadData() {
		$data = CM_Mysql::select(TBL_CM_SPLITTEST, '*', array('name' => $this->getName()))->fetchAssoc();
		if ($data) {
			$data['variations'] = CM_Mysql::select(TBL_CM_SPLITTESTVARIATION, array('id',
				'name'), array('splittestId' => $data['id']))->fetchAllTree();
		}
		return $data;
	}

	protected static function _create(array $data) {
		$name = (string) $data['name'];
		$variations = array_unique($data['variations']);
		if (empty($variations)) {
			throw new CM_Exception('Cannot create splittest without variations');
		}

		$id = CM_Mysql::insert(TBL_CM_SPLITTEST, array('name' => $name, 'createStamp' => time()));
		try {
			foreach ($variations as $variation) {
				CM_Mysql::insert(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $id, 'name' => $variation));
			}
		} catch (CM_Exception $e) {
			CM_Mysql::delete(TBL_CM_SPLITTEST, array('id' => $id));
			CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $id));
			throw $e;
		}
		return new static($name);
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_SPLITTEST, array('id' => $this->getId()));
		CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $this->getId()));
		CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('splittestId' => $this->getId()));
	}

	/**
	 * @param int $fixtureId
	 */
	protected function _setConversion($fixtureId) {
		$fixtureId = (int) $fixtureId;
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('conversionStamp' => time()), array('splittestId' => $this->getId(),
			'fixtureId' => $fixtureId));
	}

	/**
	 * @param int $fixtureId
	 * @param string|null   $variationName
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	protected function _getVariationFixture($fixtureId, $variationName = null) {
		$fixtureId = (int) $fixtureId;
		$cacheKey = CM_CacheConst::Splittest_VariationFixtures . '_fixtureId:' . $fixtureId;
		$cacheWrite = false;
		if (($variationFixtures = CM_CacheLocal::get($cacheKey)) === false) {
			$variationFixtures = CM_Mysql::exec('
				SELECT `variation`.`splittestId`, `variation`.`name`
				FROM TBL_CM_SPLITTESTVARIATION_FIXTURE `fixture`
				JOIN TBL_CM_SPLITTESTVARIATION `variation` ON(`variation`.`id` = `fixture`.`variationId`)
				WHERE `fixture`.`fixtureId` = ?', $fixtureId)->fetchAllTree();

			$cacheWrite = true;
		}

		if (!array_key_exists($this->getId(), $variationFixtures)) {
			if (null !== $variationName) {
				$variation = $this->getVariations()->findByName($variationName);
				if (!$variation) {
					throw new CM_Exception_Invalid('Splittest `' . $this->getId() . '` has no variation `' . $variationName . '`.');
				}
			} else {
				$variation = $this->getVariationsEnabled()->getItemRand();
				if (!$variation) {
					throw new CM_Exception_Invalid('Splittest `' . $this->getId() . '` has no enabled variations.');
				}
			}
			CM_Mysql::replace(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('splittestId' => $this->getId(), 'fixtureId' => $fixtureId,
				'variationId' => $variation->getId(), 'createStamp' => time()));
			$variationFixtures[$this->getId()] = $variation->getName();
			$cacheWrite = true;
		}

		if ($cacheWrite) {
			CM_CacheLocal::set($cacheKey, $variationFixtures);
		}

		return $variationFixtures[$this->getId()];
	}

}
