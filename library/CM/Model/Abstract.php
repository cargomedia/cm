<?php

abstract class CM_Model_Abstract extends CM_Class_Abstract implements CM_Comparable, CM_ArrayConvertible, CM_Cacheable, Serializable {

	/** @var array */
	protected $_id;

	/** @var array|null */
	private $_data;

	/** @var CM_ModelAsset_Abstract[] */
	private $_assets = array();

	/** @var boolean */
	private $_autoCommit = true;

	/** @var array|null */
	protected $_schema;

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

	final public function delete() {
		foreach ($this->_assets as $asset) {
			$asset->_onModelDelete();
		}
		$containingCacheables = $this->_getContainingCacheables();
		$this->_onBeforeDelete();
		$this->_onDelete();
		if ($cache = $this->getCache()) {
			$cache->delete($this->getIdRaw());
		}
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
	public function getIdRaw() {
		return $this->_id;
	}

	/**
	 * @param CM_Comparable|null $model
	 * @return boolean
	 */
	final public function equals(CM_Comparable $model = null) {
		if (empty($model)) {
			return false;
		}
		/** @var CM_Model_Abstract $model */
		return (get_class($this) == get_class($model) && $this->_getId() === $model->_getId());
	}

	final public function serialize() {
		return serialize(array($this->_id, $this->_data));
	}

	final public function unserialize($data) {
		list($id, $this->_data) = unserialize($data);
		$this->_construct($id);
	}

	/**
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	public function getCache() {
		return static::getCacheStatic();
	}

	/**
	 * @return CM_Model_Abstract
	 */
	final public function _change() {
		if ($cache = $this->getCache()) {
			$cache->delete($this->getIdRaw());
		}
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
		if (null === $this->_data) {
			if ($cache = $this->getCache()) {
				if (false !== ($data = $cache->load($this->getIdRaw()))) {
					$this->_data = $data;
				}
			}
			if (null === $this->_data) {
				if (is_array($data = $this->_loadData())) {
					$this->_data = $data;
				}
				if (null === $this->_data) {
					throw new CM_Exception_Nonexistent(get_called_class() . ' `' . CM_Util::var_line($this->_getId(), true) . '` has no data.');
				}

				$this->_autoCommit = false;
				/** @var CM_ModelAsset_Abstract $asset */
				foreach ($this->_assets as $asset) {
					$asset->_loadAsset();
				}
				$this->_autoCommit = true;

				if ($cache) {
					$cache->save($this->getIdRaw(), $this->_data);
				}
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
	 * @param string|array $data
	 * @param mixed|null   $value
	 */
	final public function _set($data, $value = null) {
		if (null !== $value) {
			$data = array($data => $value);
		}
		$this->_get(); // Make sure data is loaded

		foreach ($data as $field => $value) {
			$this->_data[$field] = $value;
		}

		if ($this->_autoCommit) {
			if ($cache = $this->getCache()) {
				$cache->save($this->getIdRaw(), $this->_data);
			}
			if ($this->_isSchemaField(array_keys($data))) {
				$this->_onChange();
			}
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

	/**
	 * @return CM_Cacheable[]
	 */
	protected function _getContainingCacheables() {
		return array();
	}

	/**
	 * @return array|null
	 */
	protected function _getSchema() {
		return $this->_schema;
	}

	/**
	 * @param string|string[] $field
	 * @return bool
	 */
	protected function _isSchemaField($field) {
		$schema = $this->_getSchema();
		if (null === $schema) {
			return true;
		}
		if (is_array($field)) {
			return count(array_intersect($field, array_keys($schema))) > 0;
		}
		return array_key_exists($field, $schema);
	}

	/**
	 * @param array|null $data
	 * @return static
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
	 * @param int        $type
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
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	public static function getCacheStatic() {
		return new CM_Model_StorageAdapter_Cache(get_called_class());
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
