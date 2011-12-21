<?php

class CM_TreeNode_Config extends CM_TreeNode_Abstract {
	public function Section($name) {
		return $this->getNode($name);
	}

	public function get($key) {
		try {
			return $this->getLeaf($key);
		} catch (CM_TreeException $e) {
			throw new CM_ConfigException('Undefined config var ' . $key);
		}
	}
	public function __get($key) {
		return $this->get($key);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public function set($key, $value) {
		$key = trim($key);

		$query = CM_Mysql::placeholder('UPDATE `' . TBL_CONFIG . '` SET `value`="?"
				WHERE `config_section_id`=? AND `name`="?"', json_encode($value), $this->getId(), $key);
		CM_Mysql::query($query);
		$result = (bool) CM_Mysql::affected_rows();

		if ($result) {
			$this->setLeaf($key, $value);

			CM_Config::cleanCache($this->getId(), $key);
		}

		return $result;
	}

	/**
	 * Returns section configs list as an assocciative array with config var names at index.
	 * Each item of the array is an object of stdClass.
	 *
	 * @return array
	 */
	public function getConfigsList() {
		$cacheKey = CM_CacheConst::Configs_Section . '_configsList_section:' . $this->getId();
		$configs_list = CM_Cache::get($cacheKey);
		if ($configs_list === false) {
			$configs_list = array();

			$query = CM_Mysql::placeholder('SELECT * FROM `' . TBL_CONFIG . '` WHERE `config_section_id`=?', $this->getId());

			$result = CM_Mysql::query($query);

			while ($_conf = $result->fetchObject()) {
				$_conf->value = json_decode($_conf->value);
				$configs_list[$_conf->name] = $_conf;
			}

			CM_Cache::set($cacheKey, $configs_list);
		}

		return $configs_list;
	}

	/**
	 * Returns an information about the section.
	 *
	 * @return object of stdClass
	 */
	public function getSectionInfo() {
		$cacheKey = CM_CacheConst::Configs_Section . '_info_section:' . $this->getId();
		$result = CM_Cache::get($cacheKey);
		if ($result === false) {
			$query = CM_Mysql::placeholder('SELECT `section`,`label`,`parent_section_id`,`config_section_id`
					FROM `'
				. TBL_CONFIG_SECTION . '`
					WHERE `config_section_id`=?', $this->getId());
			$result = CM_Mysql::query($query)->fetchObject();

			CM_Cache::set($cacheKey, $result);
		}

		return $result;
	}
}

class CM_ConfigException extends Exception {
}
