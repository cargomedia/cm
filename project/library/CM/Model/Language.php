<?php

class CM_Model_Language extends CM_Model_Abstract {

	protected function _loadData() {
		$data = CM_Mysql::select(TBL_CM_LANGUAGE, '*', array('id' => $this->getId()))->fetchAssoc();
		if ($data) {
			$data['translations'] = array();
			foreach ($this->getTranslations() as $translation) {
				$data['translations'][$translation['key']] = $translation['value'];
			}
		}
		return $data;
	}

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
	 * @return int
	 */
	public function getEnabled() {
		return (int) $this->_get('enabled');
	}

	/**
	 * @return CM_Paging_Translation_Language
	 */
	public function getTranslations() {
		return new CM_Paging_Translation_Language($this);
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getTranslation($key) {
		$translations = $this->_get('translations');
		if (!array_key_exists($key, $translations)) {
			static::_addKey($key);
			$this->_change();
		}
		if (is_null($translations[$key])) {
			return $key;
		}
		return $translations[$key];
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setTranslation($key, $value) {
		$languageKeyId = CM_Mysql::select(TBL_CM_LANGUAGEKEY, 'id', array('name' => $key))->fetchOne();
		if (!$languageKeyId) {
			$languageKeyId = static::_addKey($key);
		}

		CM_Mysql::insert(TBL_CM_LANGUAGEVALUE, array('value' => $value, 'languageKeyId' => $languageKeyId, 'languageId' => $this->getId()), null, array('value' => $value));
		$this->_change();
	}

	/**
	 * @param string $name
	 * @return int
	 */
	protected static function _addKey($name) {
		$name = (string) $name;
		$languageKeyId = CM_Mysql::insert(TBL_CM_LANGUAGEKEY, array('name' => $name, 'accessStamp' => time()), null, array('accessStamp' => time()));
		/** @var CM_Model_Language $language */
		foreach (new CM_Paging_Language_All() as $language) {
			$language->_change();
		}
		return $languageKeyId;
	}

	/**
	 * @param string	$name
	 * @param string	$abbreviation
	 * @param bool		$enabled
	 */
	public function setData($name, $abbreviation, $enabled = false) {
		$name = (string) $name;
		$abbreviation = (string) $abbreviation;
		$enabled = (bool) $enabled;
		CM_Mysql::update(TBL_CM_LANGUAGE, array('name' => $name, 'abbreviation' => $abbreviation, 'enabled' => $enabled), array('id' => $this->getId()));
		$this->_change();
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

	protected function _onChange() {
		$this->getTranslations()->_change();
	}

	protected function _getContainingCacheables() {
		$cacheables = parent::_getContainingCacheables();
		$cacheables[] = new CM_Paging_Language_All();
		return $cacheables;
	}

	protected static function _create(array $data) {
		$data = CM_Params::factory($data);
		$id = CM_Mysql::insert(TBL_CM_LANGUAGE, array('name' => $data->getString('name'), 'abbreviation' => $data->getString('abbreviation')));
		return new static($id);
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_LANGUAGE, array('id' => $this->getId()));
		CM_Mysql::delete(TBL_CM_LANGUAGEVALUE, array('languageId' => $this->getId()));
	}

}