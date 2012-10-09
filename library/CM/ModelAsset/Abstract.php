<?php

abstract class CM_ModelAsset_Abstract extends CM_Class_Abstract implements CM_Cacheable {
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
	 * Call on model data change
	 *
	 * @return CM_ModelAsset_Abstract
	 */
	public function _change() {
		$this->_model->_change();
		return $this;
	}

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
}
