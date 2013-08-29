<?php

abstract class CM_Model_Abstract extends CM_Class_Abstract implements CM_Comparable, CM_ArrayConvertible, CM_Cacheable, Serializable {

	/** @var array|null */
	protected $_id;

	/** @var array|null */
	private $_data;

	/** @var CM_ModelAsset_Abstract[] */
	private $_assets = array();

	/** @var boolean */
	private $_autoCommit = true;

	/**
	 * @param int|null   $id
	 * @param array|null $data
	 */
	public function __construct($id = null, array $data = null) {
		if (null !== $id) {
			$id = array('id' => (int) $id);
		}
		$this->_construct($id, $data);
	}

	/**
	 * @param array|null $id
	 * @param array|null $data
	 * @throws CM_Exception_Invalid
	 */
	final protected function _construct(array $id = null, array $data = null) {
		if (null === $id && null === $data) {
			$data = array();
			$this->_autoCommit = false;
		}
		$this->_id = $id;
		if (null !== $data) {
			$this->_data = $this->_validateFields($data);
		}
		foreach ($this->_getAssets() as $asset) {
			$this->_assets = array_merge($this->_assets, array_fill_keys($asset->getClassHierarchy(), $asset));
		}
		$this->_get(); // Make sure data can be loaded
	}

	public function commit() {
		$persistence = $this->getPersistence();
		if (!$persistence) {
			throw new CM_Exception_Invalid('Cannot create model without persistence');
		}
		if ($this->hasId()) {
			$persistence->save($this->getType(), $this->getIdRaw(), $this->_getSchemaData());

			if ($cache = $this->getCache()) {
				$cache->save($this->getType(), $this->getIdRaw(), $this->_data);
			}
			$this->_onChange();
		} else {
			$this->_id = $persistence->create($this->getType(), $this->_getSchemaData());

			if ($cache = $this->getCache()) {
				$this->_loadAssets(true);
				$cache->save($this->getType(), $this->getIdRaw(), $this->_data);
			}
			$this->_onChange();
			foreach ($this->_getContainingCacheables() as $cacheable) {
				$cacheable->_change();
			}
			$this->_onCreate();
		}
		$this->_autoCommit = true;
	}

	final public function delete() {
		foreach ($this->_assets as $asset) {
			$asset->_onModelDelete();
		}
		$containingCacheables = $this->_getContainingCacheables();
		$this->_onBeforeDelete();
		$this->_onDelete();
		if ($persistence = $this->getPersistence()) {
			$persistence->delete($this->getType(), $this->getIdRaw());
		}
		if ($cache = $this->getCache()) {
			$cache->delete($this->getType(), $this->getIdRaw());
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
	 * @throws CM_Exception_Invalid
	 */
	public function getIdRaw() {
		if (null === $this->_id) {
			throw new CM_Exception_Invalid('Model has no id');
		}
		return $this->_id;
	}

	/**
	 * @return bool
	 */
	public function hasId() {
		return (null !== $this->_id);
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
		return serialize(array($this->getIdRaw(), $this->_data));
	}

	final public function unserialize($serialized) {
		list($id, $data) = unserialize($serialized);
		$this->_construct($id, $data);
	}

	/**
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	public function getCache() {
		return self::_getStorageAdapter(static::getCacheClass());
	}

	/**
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	public function getPersistence() {
		return self::_getStorageAdapter(static::getPersistenceClass());
	}

	/**
	 * @param string|null $className
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 * @throws CM_Exception_Invalid
	 */
	protected static function _getStorageAdapter($className = null) {
		if (null === $className) {
			return null;
		}
		if (!class_exists($className) || !is_subclass_of($className, 'CM_Model_StorageAdapter_AbstractAdapter')) {
			throw new CM_Exception_Invalid('Invalid storage adapter class `' . $className . '`');
		}
		return new $className();
	}

	/**
	 * @return CM_Model_Abstract
	 */
	final public function _change() {
		if ($cache = $this->getCache()) {
			$cache->delete($this->getType(), $this->getIdRaw());
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
				if (false !== ($data = $cache->load($this->getType(), $this->getIdRaw()))) {
					$this->_data = $this->_validateFields($data);
				}
			}
			if (null === $this->_data) {
				if ($persistence = $this->getPersistence()) {
					if (false !== ($data = $persistence->load($this->getType(), $this->getIdRaw()))) {
						$this->_data = $this->_validateFields($data);
					}
				}
				if (null === $this->_data) {
					if (is_array($data = $this->_loadData())) {
						$this->_data = $this->_validateFields($data);
					}
					if (null === $this->_data) {
						throw new CM_Exception_Nonexistent(get_called_class() . ' `' . CM_Util::var_line($this->_getId(), true) . '` has no data.');
					}
				}

				if ($cache) {
					$this->_loadAssets(true);
					$cache->save($this->getType(), $this->getIdRaw(), $this->_data);
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

		foreach ($this->_validateFields($data) as $field => $value) {
			$this->_data[$field] = $value;
		}

		if ($this->_autoCommit) {
			if ($cache = $this->getCache()) {
				$cache->save($this->getType(), $this->getIdRaw(), $this->_data);
			}
			if ($persistence = $this->getPersistence()) {
				$persistence->save($this->getType(), $this->getIdRaw(), $this->_getSchemaData());
			}
			$this->_onChange();
		}
	}

	/**
	 * @return array
	 */
	protected function _loadData() {
		throw new CM_Exception_NotImplemented();
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
	protected function _getAssets() {
		return array();
	}

	protected function _create() {
		$this->_id = null;
		$this->commit();
	}

	/**
	 * @param string|null $key
	 * @return array|mixed
	 *
	 * @throws CM_Exception_Invalid
	 */
	final protected function _getId($key = null) {
		$idRaw = $this->getIdRaw();
		if (null === $key) {
			return $idRaw;
		}
		$key = (string) $key;
		if (!array_key_exists($key, $idRaw)) {
			throw new CM_Exception_Invalid('Id-array has no field `' . $key . '`.');
		}
		return $idRaw[$key];
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
	 * @param bool|null $disableAutoCommit
	 */
	protected function _loadAssets($disableAutoCommit = null) {
		$autoCommitBackup = $this->_autoCommit;
		if ($disableAutoCommit) {
			$this->_autoCommit = false;
		}
		/** @var CM_ModelAsset_Abstract $asset */
		foreach ($this->_assets as $asset) {
			$asset->_loadAsset();
		}
		$this->_autoCommit = $autoCommitBackup;
	}

	/**
	 * @return CM_Cacheable[]
	 */
	protected function _getContainingCacheables() {
		return array();
	}

	/**
	 * @return CM_Model_Schema_Definition|null
	 */
	protected function _getSchema() {
		return null;
	}

	/**
	 * @return array
	 * @throws CM_Exception_Invalid
	 */
	protected function _getSchemaData() {
		if (null === $this->_data) {
			throw new CM_Exception_Invalid('Model has no data');
		}
		if (!$schema = $this->_getSchema()) {
			throw new CM_Exception_Invalid('Cannot get schema-data without a schema');
		}
		return array_intersect_key($this->_data, array_flip($schema->getFieldNames()));
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function _validateFields(array $data) {
		$schema = $this->_getSchema();
		if (!$schema) {
			return $data;
		}
		foreach ($data as $key => &$value) {
			$value = $schema->validateField($key, $value);
		}
		return $data;
	}

	/**
	 * @param array|null $data
	 * @return static
	 */
	final public static function createStatic(array $data = null) {
		if ($data === null) {
			$data = array();
		}
		$model = static::_createStatic($data);
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
		return $className::createStatic($data);
	}

	/**
	 * @return string|null
	 */
	public static function getCacheClass() {
		return 'CM_Model_StorageAdapter_Cache';
	}

	/**
	 * @return string|null
	 */
	public static function getPersistenceClass() {
		return null;
	}

	/**
	 * @param int $type
	 * @return string
	 */
	public static function getClassName($type) {
		return self::_getClassName($type);
	}

	/**
	 * @param array $data
	 * @return CM_Model_Abstract
	 * @throws CM_Exception_NotImplemented
	 */
	protected static function _createStatic(array $data) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param int        $type
	 * @param array      $id
	 * @param array|null $data
	 * @return CM_Model_Abstract
	 */
	final public static function factoryGeneric($type, array $id, array $data = null) {
		$className = self::_getClassName($type);
		/*
		 * Cannot use __construct(), since signature is unknown.
		 * unserialize() is ~10% slower.
		 */
		$serialized = serialize(array($id, $data));
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
