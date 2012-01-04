<?php

class CM_ModelAsset_User_Preferences extends CM_ModelAsset_User_Abstract {

	public function _loadAsset() {
		$this->getAll();
	}

	public function _onModelDelete() {
		CM_Mysql::delete(TBL_CM_USER_PREFERENCE, array('userId' => $this->_model->getId()));
	}

	/**
	 * @param string $section
	 * @param string $key
	 * @return boolean
	 */
	public function get($section, $key) {
		$values = $this->getAll();
		if (!isset($values[$section][$key])) {
			throw new CM_Exception("Invalid preference ($section.$key)");
		}
		return $values[$section][$key]['value'];
	}

	/**
	 * @param string $section
	 * @param string $key
	 * @param boolean $value
	 */
	public function set($section, $key, $value) {
		$value = (bool) $value;
		$defaults = self::getDefaults();
		if (!isset($defaults[$section][$key])) {
			throw new CM_Exception("Invalid preference ($section.$key)");
		}
		if ($value == $defaults[$section][$key]['value']) {
			CM_Mysql::delete(TBL_CM_USER_PREFERENCE, array('userId' => $this->_model->getId(), 'preferenceId' => $defaults[$section][$key]['id']));
		} else {
			CM_Mysql::replace(TBL_CM_USER_PREFERENCE,
					array('userId' => $this->_model->getId(), 'preferenceId' => $defaults[$section][$key]['id'], 'value' => $value));
		}
		$this->_change();
	}

	/**
	 * @return array
	 */
	public function getAll() {
		if (($values = $this->_cacheGet('values')) === false) {
			$values = self::getDefaults();
			$valuesSpecific = CM_Mysql::select(TBL_CM_USER_PREFERENCE, array('preferenceId', 'value'), array('userId' => $this->_model->getId()))
					->fetchAllTree();
			foreach ($values as &$section) {
				foreach ($section as &$key) {
					if (isset($valuesSpecific[$key['id']])) {
						$key['value'] = (bool) $valuesSpecific[$key['id']];
					}
				}
			}
			$this->_cacheSet('values', $values);
		}
		return $values;
	}

	public function reset() {
		CM_Mysql::delete(TBL_CM_USER_PREFERENCE, array('userId' => $this->_model->getId()));
		$this->_change();
	}

	/**
	 * @return array of arrays
	 */
	public static function getDefaults() {
		$cacheKey = CM_CacheConst::User_Asset_Preferences_Defaults;
		if (($defaults = CM_CacheLocal::get($cacheKey)) === false) {
			$defaults = array();
			$rows = CM_Mysql::select(TBL_CM_USER_PREFERENCEDEFAULT, array('section', 'key', 'preferenceId', 'defaultValue', 'configurable'))->fetchAll();
			foreach ($rows as $default) {
				if (!isset($defaults[$default['section']])) {
					$defaults[$default['section']] = array();
				}
				$defaults[$default['section']][$default['key']] = array('id' => (int) $default['preferenceId'],
						'value' => (bool) $default['defaultValue'], 'configurable' => (boolean) $default['configurable']);
			}
			CM_CacheLocal::set($cacheKey, $defaults);
		}
		return $defaults;
	}
	
	/**
	 * @return array
	 */
	public static function getStats() {
		return CM_Mysql::exec("SELECT `preferenceId`, COUNT(*) AS `count`, `value` FROM TBL_CM_USER_PREFERENCE GROUP BY `preferenceId`, `value`")->fetchAllTree();
	}
}
