<?php

class CM_Model_Language extends CM_Model_Abstract {
	const TYPE = 23;

	/** @var CM_Model_Language|null $_backup */
	private $_backup;

	/**
	 * @return string
	 */
	public function getName() {
		return (string) $this->_get('name');
	}

	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return (string) $this->_get('abbreviation');
	}

	/**
	 * @return bool
	 */
	public function getEnabled() {
		return (bool) $this->_get('enabled');
	}

	/**
	 * @return CM_Paging_Translation_Language
	 */
	public function getTranslations() {
		return new CM_Paging_Translation_Language($this);
	}

	/**
	 * @param string      $key
	 * @param bool|null   $skipCacheLocal
	 * @return string
	 */
	public function getTranslation($key, $skipCacheLocal = null) {
		$cacheKey = CM_CacheConst::Language_Translations . '_languageId:' . $this->getId();
		if ($skipCacheLocal || false === ($translations = CM_CacheLocal::get($cacheKey))) {
			$translations = $this->getTranslations()->getAssociativeArray();

			if (!$skipCacheLocal) {
				CM_CacheLocal::set($cacheKey, $translations);
			}
		}

		if (!array_key_exists($key, $translations)) {
			static::_setKey($key);
			$this->_change();
		}
		if (is_null($translations[$key])) {
			if (!$this->getBackup()) {
				return $key;
			}
			return $this->getBackup()->getTranslation($key, $skipCacheLocal);
		}
		return $translations[$key];
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setTranslation($key, $value) {
		$languageKeyId = static::_setKey($key);

		CM_Mysql::insert(TBL_CM_LANGUAGEVALUE, array('value' => $value, 'languageKeyId' => $languageKeyId,
			'languageId' => $this->getId()), null, array('value' => $value));
		$this->_change();
	}

	/**
	 * @param string $key
	 */
	public function unsetTranslation($key) {
		$languageKeyId = static::_setKey($key);
		CM_Mysql::delete(TBL_CM_LANGUAGEVALUE, array('languageKeyId' => $languageKeyId, 'languageId' => $this->getId()));
		$this->_change();
	}

	/**
	 * @param string                             $name
	 * @param string                             $abbreviation
	 * @param bool|null                          $enabled
	 * @param CM_Model_Language|null             $backup
	 */
	public function setData($name, $abbreviation, $enabled = null, CM_Model_Language $backup = null) {
		$name = (string) $name;
		$abbreviation = (string) $abbreviation;
		$enabled = (bool) $enabled;
		$backupId = ($backup) ? $backup->getId() : null;
		CM_Mysql::update(TBL_CM_LANGUAGE, array('name' => $name, 'abbreviation' => $abbreviation, 'enabled' => $enabled,
			'backupId' => $backupId), array('id' => $this->getId()));
		$this->_change();
	}

	/**
	 * @return CM_Model_Language|null
	 */
	public function getBackup() {
		if (!$this->_backup && $this->_get('backupId')) {
			$this->_backup = new CM_Model_Language($this->_get('backupId'));
		}
		return $this->_backup;
	}

	/**
	 * @param CM_Model_Language $language
	 * @return bool
	 */
	public function isBackedUpBy(CM_Model_Language $language) {
		while (!is_null($language)) {
			if ($this->equals($language)) {
				return true;
			}
			$language = $language->getBackup();
		}
		return false;
	}

	public static function flushCacheLocal() {
		CM_CacheLocal::flush();
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_LANGUAGE, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onChange() {
		$this->getTranslations()->_change();
	}

	protected function _getContainingCacheables() {
		$cacheables = parent::_getContainingCacheables();
		$cacheables[] = new CM_Paging_Language_All();
		return $cacheables;
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_LANGUAGE, array('id' => $this->getId()));
		CM_Mysql::delete(TBL_CM_LANGUAGEVALUE, array('languageId' => $this->getId()));
		CM_Mysql::update(TBL_CM_USER, array('languageId' => null), array('languageId' => $this->getId()));
	}

	/**
	 * @param string      $abbreviation
	 * @return CM_Model_Language|null
	 */
	public static function findByAbbreviation($abbreviation) {
		$abbreviation = (string) $abbreviation;
		$languageId = CM_Mysql::select(TBL_CM_LANGUAGE, 'id', array('abbreviation' => $abbreviation))->fetchOne();
		if (!$languageId) {
			return null;
		}
		return new static($languageId);
	}

	/**
	 * @return CM_Model_Language|null
	 */
	public static function findDefault() {
		$cacheKey = CM_CacheConst::Language_Default;
		if (false === ($languageId = CM_CacheLocal::get($cacheKey))) {
			$languageId = CM_Mysql::select(TBL_CM_LANGUAGE, 'id', array('enabled' => true))->fetchOne();
			CM_CacheLocal::set($cacheKey, $languageId);
		}
		if (!$languageId) {
			return null;
		}
		return new static($languageId);
	}

	/**
	 * @param string $name
	 */
	public static function deleteKey($name) {
		$name = (string) $name;
		$languageKeyId = CM_Mysql::select(TBL_CM_LANGUAGEKEY, 'id', array('name' => $name))->fetchOne();
		CM_Mysql::delete(TBL_CM_LANGUAGEVALUE, array('languageKeyId' => $languageKeyId));
		CM_Mysql::delete(TBL_CM_LANGUAGEKEY, array('id' => $languageKeyId));
		/** @var CM_Model_Language $language */
		foreach (new CM_Paging_Language_All() as $language) {
			$language->_change();
		}
	}

	protected static function _create(array $data) {
		$params = CM_Params::factory($data);
		$backupId = ($params->has('backup')) ? $params->getLanguage('backup')->getId() : null;
		$id = CM_Mysql::insert(TBL_CM_LANGUAGE, array('name' => $params->getString('name'), 'abbreviation' => $params->getString('abbreviation'),
			'enabled' => $params->getBoolean('enabled'), 'backupId' => $backupId));
		return new static($id);
	}

	/**
	 * @param string $name
	 * @return int
	 */
	protected static function _setKey($name) {
		$name = (string) $name;
		$languageKeyId = CM_Mysql::select(TBL_CM_LANGUAGEKEY, 'id', array('name' => $name))->fetchOne();
		if (!$languageKeyId) {
			$languageKeyId = CM_Mysql::insert(TBL_CM_LANGUAGEKEY, array('name' => $name), null, array());
			/** @var CM_Model_Language $language */
			foreach (new CM_Paging_Language_All() as $language) {
				$language->_change();
			}
		}
		return $languageKeyId;
	}
}