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
	 * @return bool
	 */
	public function getRunning() {
		return (bool) $this->_get('running');
	}

	/**
	 * @param bool $state
	 */
	public function setRunning($state) {
		$state = (bool) $state;
		CM_Mysql::update(TBL_CM_SPLITTEST, array('running' => $state), array('id' => $this->getId()));
		$this->_change();
	}

	/**
	 * @return string[]
	 */
	public function getVariations() {
		return $this->_get('variations');
	}

	/**
	 * @param CM_Model_User $user
	 * @return string
	 */
	public function getVariationFixture(CM_Model_User $user,$t) {
		$cacheKey = CM_CacheConst::Splittest_VariationFixtures . '_userId:' . $user->getId();
		if (($variationFixtures = CM_Cache::get($cacheKey)) === false) {
			$variationFixtures = CM_Mysql::select(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId',
				'variationId'), array('userId' => $user->getId()))->fetchAllTree();

			CM_Cache::set($cacheKey, $variationFixtures);
		}

		$variations = $this->getVariations();
		if (array_key_exists($this->getId(), $variationFixtures)) {
			$variationId = $variationFixtures[$this->getId()];
		} else {
			$variationIds = array_keys($variations);
			$variationId = $variationIds[array_rand($variationIds)];
			CM_Mysql::replace(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->getId(), 'userId' => $user->getId(),
				'variationId' => $variationId, 'createStamp' => $t));
			CM_Cache::delete($cacheKey);
		}

		if (!array_key_exists($variationId, $variations)) {
			throw new CM_Exception_Invalid('Unknown variation `' . $variationId . '` for splittest `' . $this->getId() . '`.');
		}
		return $variations[$variationId];
	}

	/**
	 * @param int $variationId
	 * @return int
	 */
	public function getVariationFixtureCount($variationId) {
		$variationId = (int) $variationId;
		return CM_Mysql::count(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->getId(), 'variationId' => $variationId));
	}

	/**
	 * @return int
	 */
	public function getVariationFixtureMin() {
		return (int) CM_Mysql::exec(
			'SELECT MIN(`createStamp`) FROM TBL_CM_SPLITTESTVARIATION_USER WHERE `splittestId` = ' . $this->getId())->fetchOne();
	}

	/**
	 * @param int $variationId
	 * @return int
	 */
	public function getConversionCount($variationId) {
		$variationId = (int) $variationId;
		return (int) CM_Mysql::exec('SELECT COUNT(1) FROM TBL_CM_SPLITTESTVARIATION_USER WHERE `splittestId`=? AND `variationId`=? AND `conversionStamp` IS NOT NULL', $this->getId(), $variationId)->fetchOne();
	}

	/**
	 * @param CM_Model_User $user
	 */
	public function setConversion(CM_Model_User $user,$t) {
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION_USER, array('conversionStamp' => $t), array('splittestId' => $this->getId(),
			'userId' => $user->getId()));
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
		try {
			$id = CM_Mysql::insert(TBL_CM_SPLITTEST, array('name' => $name, 'createStamp' => time()));
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
