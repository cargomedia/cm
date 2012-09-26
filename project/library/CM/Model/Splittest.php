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
	 * @param CM_Model_User $user
	 * @param string|null   $variationName
	 * @throws CM_Exception_Invalid
	 * @return string
	 */
	public function getVariationFixture(CM_Model_User $user, $variationName = null) {
		$cacheKey = CM_CacheConst::Splittest_VariationFixtures . '_userId:' . $user->getId();
		$cacheWrite = false;
		if (($variationFixtures = CM_CacheLocal::get($cacheKey)) === false) {
			$variationFixtures = CM_Mysql::exec('
				SELECT `variation`.`splittestId`, `variation`.`name`
				FROM TBL_CM_SPLITTESTVARIATION_USER `fixture`
				JOIN TBL_CM_SPLITTESTVARIATION `variation` ON(`variation`.`id` = `fixture`.`variationId`)
				WHERE `fixture`.`userId` = ?', $user->getId())->fetchAllTree();

			$cacheWrite = true;
		}

		if (!array_key_exists($this->getId(), $variationFixtures)) {
			if ($variationName) {
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
			CM_Mysql::replace(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->getId(), 'userId' => $user->getId(),
				'variationId' => $variation->getId(), 'createStamp' => time()));
			$variationFixtures[$this->getId()] = $variation->getName();
			$cacheWrite = true;
		}

		if ($cacheWrite) {
			CM_CacheLocal::set($cacheKey, $variationFixtures);
		}

		return $variationFixtures[$this->getId()];
	}

	/**
	 * @return int
	 */
	public function getVariationFixtureCreatedMin() {
		return (int) CM_Mysql::exec(
			'SELECT MIN(`createStamp`) FROM TBL_CM_SPLITTESTVARIATION_USER WHERE `splittestId` = ' . $this->getId())->fetchOne();
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
	 * @param CM_Model_User $user
	 */
	public function setConversion(CM_Model_User $user) {
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION_USER, array('conversionStamp' => time()), array('splittestId' => $this->getId(),
			'userId' => $user->getId()));
	}

	/**
	 * @param int $id
	 * @return CM_Model_Splittest
	 * @throws CM_Exception_Nonexistent
	 */
	public static function findId($id) {
		$id = (int) $id;
		$name = CM_Mysql::select(TBL_CM_SPLITTEST, 'name', array('id' => $id))->fetchOne();
		if (false === $name) {
			throw new CM_Exception_Nonexistent('Cannot find splittest with id `' . $id . '`');
		}
		return new self($name);
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
		CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->getId()));
	}
}
