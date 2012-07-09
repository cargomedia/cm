<?php

class CM_Model_Language extends CM_Model_Abstract {
	const TYPE = 23;

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
	 * @param array|null  $params
	 * @param bool|null   $skipCacheLocal
	 * @return string
	 */
	public function getTranslation($key, array $params = null, $skipCacheLocal = null) {
		$cacheKey = CM_CacheConst::Language . '_languageId:' . $this->getId();
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
			return $this->getBackup()->getTranslation($key, $params, $skipCacheLocal);
		}
		if ($params) {
			return $this->_parseVariables($translations[$key], $params);
		} else {
			return $translations[$key];
		}
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
	 * @param string                   $name
	 * @param string                   $abbreviation
	 * @param bool|null                $enabled
	 * @param integer|null             $backupId
	 * @return void
	 */
	public function setData($name, $abbreviation, $enabled = null, $backupId = null) {
		$name = (string) $name;
		$abbreviation = (string) $abbreviation;
		$enabled = (bool) $enabled;
		CM_Mysql::update(TBL_CM_LANGUAGE, array('name' => $name, 'abbreviation' => $abbreviation,
			'enabled' => $enabled, 'backupId' => $backupId), array('id' => $this->getId()));
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
	}

	/**
	 * @param string       $key
	 * @param array        $variables
	 * @return string
	 */
	private function _parseVariables($key, array $variables) {
		return preg_replace('~\{\$(\w+)(->\w+\(.*?\))?\}~ie', "isset(\$variables['\\1']) ? \$variables['\\1']\\2 : '\\0'", $key);
	}

	/**
	 * @param string $abbreviation
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
		$languageId = CM_Mysql::select(TBL_CM_LANGUAGE, 'id')->fetchOne();
		if (!$languageId) {
			return null;
		}
		return new static($languageId);
	}

	protected static function _create(array $data) {
		$data = CM_Params::factory($data);
		$backupId = ($data->has('backupId')) ? $data->getLanguage('backupId')->getId() : null;
		$id = CM_Mysql::insert(TBL_CM_LANGUAGE, array('name' => $data->getString('name'), 'abbreviation' => $data->getString('abbreviation'),
			'enabled' => $data->getBoolean('enabled'), 'backupId' => $backupId));
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

	public static function clearCache() {
		CM_CacheLocal::flush();
	}
}