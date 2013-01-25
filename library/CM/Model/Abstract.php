<?php

abstract class CM_Model_Abstract extends CM_Class_Abstract implements CM_Comparable, CM_ArrayConvertible, CM_Cacheable, Serializable {

	/**
	 * @var array $_id
	 */
	protected $_id;

	/**
	 * @var CM_Cache_Abstract $_cacheClass
	 */
	private $_cacheClass = 'CM_Cache';

	/**
	 * @var array $_data
	 */
	private $_data;

	/**
	 * @var CM_ModelAsset_Abstract[]
	 */
	private $_assets = array();

	/**
	 * @var boolean $_autoCommitCache
	 */
	private $_autoCommitCache = true;

	/**
	 * @param int $id
	 */
	public function __construct($id) {
		$this->_construct(array('id' => (int) $id));
	}

	/**
	 * @param array $id
	 */
	final protected function _construct(array $id) {
		$this->_id = $id;
		foreach ($this->_loadAssets() as $asset) {
			$this->_assets = array_merge($this->_assets, array_fill_keys($asset->getClassHierarchy(), $asset));
		}
		$this->_get(); // Make sure data can be loaded
	}

	/**
	 * @return array
	 */
	abstract protected function _loadData();

	public function delete() {
		foreach ($this->_assets as $asset) {
			$asset->_onModelDelete();
		}
		$containingCacheables = $this->_getContainingCacheables();
		$this->_onBeforeDelete();
		$this->_onDelete();
		$cacheClass = $this->_cacheClass;
		$cacheClass::delete($this->_getCacheKey());
		foreach ($containingCacheables as $cacheable) {
			$cacheable->_change();
		}
		$this->_data = null;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->_getId('id');
	}


	/**
	 * @return array
	 */
	final public function getIdRaw() {
		return $this->_id;
	}

	/**
	 * @param CM_Comparable|null $model
	 * @return boolean
	 */
	public function equals(CM_Comparable $model = null) {
		if (empty($model)) {
			return false;
		}
		/** @var CM_Model_Abstract $model */
		return (get_class($this) == get_class($model) && $this->_getId() === $model->_getId());
	}

	public function serialize() {
		return serialize(array($this->_id, $this->_data));
	}

	public function unserialize($data) {
		list($id, $this->_data) = unserialize($data);
		$this->_construct($id);
	}

	/**
	 * @return CM_Model_Abstract
	 */
	public function _change() {
		$cacheClass = $this->_cacheClass;
		$cacheClass::delete($this->_getCacheKey());
		$this->_data = null;
		$this->_onChange();
		return $this;
	}

	/**
	 * @param string $field
	 * @return mixed
	 * @throws CM_Exception|CM_Exception_Nonexistent
	 */
	final public function _get($field = null) {
		if (!$this->_data) {
			$cacheKey = $this->_getCacheKey();
			$cacheClass = $this->_cacheClass;
			if (($this->_data = $cacheClass::get($cacheKey)) === false) {
				$this->_data = $this->_loadData();
				if (!is_array($this->_data)) {
					throw new CM_Exception_Nonexistent(get_called_class() . ' `' . CM_Util::var_line($this->_getId(), true) . '` has no data.');
				}
				$this->_autoCommitCache = false;
				/** @var CM_ModelAsset_Abstract $asset */
				foreach ($this->_assets as $asset) {
					$asset->_loadAsset();
				}
				$this->_autoCommitCache = true;
				$cacheClass::set($cacheKey, $this->_data);
			}
		}
		if ($field === null) {
			return $this->_data;
		}
		if (!array_key_exists($field, $this->_data)) {
			throw new CM_Exception('Model has no field `' . $field . '`');
		}
		return $this->_data[$field];
	}

	/**
	 * @param string $field
	 * @return boolean
	 */
	final public function _has($field) {
		$this->_get(); // Make sure data is loaded
		return array_key_exists($field, $this->_data);
	}

	/**
	 * @param string $field
	 * @param mixed  $value
	 */
	final public function _set($field, $value) {
		$this->_get(); // Make sure data is loaded
		$this->_data[$field] = $value;
		if ($this->_autoCommitCache) {
			$cacheClass = $this->_cacheClass;
			$cacheClass::set($this->_getCacheKey(), $this->_data);
		}
	}

	protected function _onBeforeDelete() {
	}

	protected function _onChange() {
	}

	protected function _onCreate() {
	}

	protected function _onDelete() {
	}

	/**
	 * @return CM_ModelAsset_Abstract[]
	 */
	protected function _loadAssets() {
		return array();
	}

	/**
	 * @param string|null $key
	 * @return array|mixed
	 *
	 * @throws CM_Exception_Invalid
	 */
	final protected function _getId($key = null) {
		if (null === $key) {
			return $this->_id;
		}
		$key = (string) $key;
		if (!array_key_exists($key, $this->_id)) {
			throw new CM_Exception_Invalid('Id-array has no field `' . $key . '`.');
		}
		return $this->_id[$key];
	}

	/**
	 * @param string $className
	 * @return boolean
	 */
	final protected function _hasAsset($className) {
		return isset($this->_assets[$className]);
	}

	/**
	 * @param string $className
	 * @return CM_ModelAsset_Abstract
	 *
	 * @throws CM_Exception
	 */
	final protected function _getAsset($className) {
		if (!$this->_hasAsset($className)) {
			throw new CM_Exception('No such asset `' . $className . '`');
		}
		return $this->_assets[$className];
	}

	final protected function _setCacheLocal() {
		$this->_cacheClass = 'CM_CacheLocal';
	}

	/**
	 * @return string
	 */
	final private function _getCacheKey() {
		return CM_CacheConst::Model . '_class:' . get_class($this) . '_id:' . serialize($this->_getId());
	}

	/**
	 * @return CM_Cacheable[]
	 */
	protected function _getContainingCacheables() {
		return array();
	}

	/**
	 * @param array|null $data
	 * @return CM_Model_Abstract
	 */
	final public static function create(array $data = null) {
		if ($data === null) {
			$data = array();
		}
		$model = static::_create($data);
		$model->_onChange();
		foreach ($model->_getContainingCacheables() as $cacheable) {
			$cacheable->_change();
		}
		$model->_onCreate();
		return $model;
	}

	/**
	 * @param int		$type
	 * @param array|null $data
	 * @return CM_Model_Abstract
	 * @throws CM_Exception_Invalid
	 */
	final public static function createType($type, array $data = null) {
		/** @var CM_Model_Abstract $className */
		$className = static::_getClassName($type);
		return $className::create($data);
	}

	/**
	 * @param array $data
	 * @return CM_Model_Abstract
	 * @throws CM_Exception_NotImplemented
	 */
	protected static function _create(array $data) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param int   $type
	 * @param array $id
	 * @return CM_Model_Abstract
	 */
	final public static function factoryGeneric($type, array $id) {
		$className = self::_getClassName($type);
		/*
		 * Cannot use __construct(), since signature is unknown.
		 * unserialize() is ~10% slower.
		 */
		$serialized = serialize(array($id, null));
		return unserialize('C:' . strlen($className) . ':"' . $className . '":' . strlen($serialized) . ':{' . $serialized . '}');
	}

	public function toArray() {
		$id = $this->_getId();
		$array = array('_type' => $this->getType(), '_id' => $id);
		if (array_key_exists('id', $id)) {
			$array['id'] = $id['id'];
		}
		return $array;
	}

	public static function fromArray(array $data) {
		return self::factoryGeneric($data['_type'], $data['_id']);
	}

}
