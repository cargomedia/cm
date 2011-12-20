<?php

abstract class CM_ModelAsset_Abstract {
	/**
	 * @var CM_Model_Abstract
	 */
	protected $_model;

	/**
	 * @param CM_Model_Abstract $model
	 */
	public function __construct(CM_Model_Abstract $model) {
		$this->_model = $model;
	}

	/**
	 * Make sure the cache gets filled within here
	 */
	abstract public function _loadAsset();

	/**
	 * Model deletion
	 */
	abstract public function _onModelDelete();

	/**
	 * @param string $key
	 * @return mixed|false
	 */
	protected function _cacheGet($key) {
		$key = get_class($this) . ':' . $key;
		if (!$this->_model->_has($key)) {
			return false;
		}
		return $this->_model->_get($key);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	protected function _cacheSet($key, $value) {
		$this->_model->_set(get_class($this) . ':' . $key, $value);
	}

	/**
	 * Call on model data change
	 *
	 * @return CM_Model_Entity_Abstract
	 */
	protected function _change() {
		return $this->_model->_change();
	}
}
