<?php

abstract class CM_Model_Abstract extends CM_Class_Abstract implements CM_Comparable, CM_ArrayConvertible, CM_Cacheable, Serializable {

	/** @var array|null */
	protected $_id;

	/** @var array|null */
	private $_data;

	/** @var array */
	private $_dataDecoded = array();

	/** @var CM_ModelAsset_Abstract[] */
	private $_assets = array();

	/** @var boolean */
	private $_autoCommit = true;

	/**
	 * @param int|null $id
	 */
	public function __construct($id = null) {
		if (null !== $id) {
			$id = array('id' => $id);
		}
		$this->_construct($id);
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
		if (null !== $id) {
			$this->_id = self::_castIdRaw($id);
		}
		if (null !== $data) {
			$this->_validateFields($data);
			$this->_setData($data);
		}
		foreach ($this->_getAssets() as $asset) {
			$this->_assets = array_merge($this->_assets, array_fill_keys($asset->getClassHierarchy(), $asset));
		}
		$this->_getData(); // Make sure data can be loaded
	}

	public function commit() {
		$persistence = $this->_getPersistence();
		if (!$persistence) {
			throw new CM_Exception_Invalid('Cannot create model without persistence');
		}
		if ($this->hasId()) {
			$persistence->save($this->getType(), $this->getIdRaw(), $this->_getSchemaData());

			if ($cache = $this->_getCache()) {
				$cache->save($this->getType(), $this->getIdRaw(), $this->_getData());
			}
			$this->_onChange();
		} else {
			$this->_id = self::_castIdRaw($persistence->create($this->getType(), $this->_getSchemaData()));

			if ($cache = $this->_getCache()) {
				$this->_loadAssets(true);
				$cache->save($this->getType(), $this->getIdRaw(), $this->_getData());
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
		$this->_onDeleteBefore();
		$this->_onDelete();
		if ($persistence = $this->_getPersistence()) {
			$persistence->delete($this->getType(), $this->getIdRaw());
		}
		if ($cache = $this->_getCache()) {
			$cache->delete($this->getType(), $this->getIdRaw());
		}
		$this->_onDeleteAfter();
		foreach ($containingCacheables as $cacheable) {
			$cacheable->_change();
		}
		$this->_data = null;
		$this->_dataDecoded = array();
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return (int) $this->_getId('id');
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
		return serialize(array($this->getIdRaw(), $this->_getData()));
	}

	final public function unserialize($serialized) {
		list($id, $data) = unserialize($serialized);
		$this->_construct($id, $data);
	}

	/**
	 * @return CM_Model_Abstract
	 */
	final public function _change() {
		if ($cache = $this->_getCache()) {
			$cache->delete($this->getType(), $this->getIdRaw());
		}
		$this->_data = null;
		$this->_dataDecoded = array();
		$this->_onChange();
		return $this;
	}

	/**
	 * @param string $field
	 * @return mixed
	 * @throws CM_Exception|CM_Exception_Nonexistent
	 */
	final public function _get($field) {
		$field = (string) $field;
		$data = $this->_getData();
		if (!array_key_exists($field, $data)) {
			throw new CM_Exception('Model has no field `' . $field . '`');
		}
		if (!array_key_exists($field, $this->_dataDecoded)) {
			$this->_dataDecoded[$field] = $this->_getSchema()->decodeField($field, $data[$field]);
		}
		return $this->_dataDecoded[$field];
	}

	/**
	 * @param string $field
	 * @return boolean
	 */
	final public function _has($field) {
		$data = $this->_getData(); // Make sure data is loaded
		return array_key_exists($field, $data);
	}

	/**
	 * @param string|array $data
	 * @param mixed|null   $value
	 * @throws CM_Exception_Invalid
	 */
	final public function _set($data, $value = null) {
		if (!is_array($data)) {
			$data = array($data => $value);
		}
		$this->_getData(); // Make sure data is loaded
		$schema = $this->_getSchema();

		foreach ($data as $key => $value) {
			$data[$key] = $schema->encodeField($key, $value);
		}
		$this->_validateFields($data);
		foreach ($data as $key => $value) {
			$this->_data[$key] = $value;
			unset($this->_dataDecoded[$key]);
		}

		if ($this->_autoCommit) {
			if ($cache = $this->_getCache()) {
				$cache->save($this->getType(), $this->getIdRaw(), $this->_getData());
			}
			if ($persistence = $this->_getPersistence()) {
				if ($schema->isEmpty()) {
					throw new CM_Exception_Invalid('Cannot save to persistence with an empty schema');
				}
				if ($schema->hasField(array_keys($data))) {
					$persistence->save($this->getType(), $this->getIdRaw(), $this->_getSchemaData());
				}
			}
			$this->_onChange();
		}
	}

	/**
	 * @return array
	 * @throws CM_Exception_Nonexistent
	 */
	protected function _getData() {
		if (null === $this->_data) {
			if ($cache = $this->_getCache()) {
				if (false !== ($data = $cache->load($this->getType(), $this->getIdRaw()))) {
					$this->_validateFields($data);
					$this->_setData($data);
				}
			}
			if (null === $this->_data) {
				if ($persistence = $this->_getPersistence()) {
					if (false !== ($data = $persistence->load($this->getType(), $this->getIdRaw()))) {
						$this->_validateFields($data);
						$this->_setData($data);
					}
				} else {
					if (is_array($data = $this->_loadData())) {
						$this->_validateFields($data);
						$this->_setData($data);
					}
				}
				if (null === $this->_data) {
					throw new CM_Exception_Nonexistent(get_called_class() . ' `' . CM_Util::var_line($this->_getId(), true) . '` has no data.');
				}

				if ($cache) {
					$this->_loadAssets(true);
					$cache->save($this->getType(), $this->getIdRaw(), $this->_data);
				}
			}
		}
		return $this->_data;
	}

	/**
	 * @param array $data
	 */
	protected function _setData(array $data) {
		$this->_data = $data;
	}

	/**
	 * @throws CM_Exception_NotImplemented
	 * @return array
	 */
	protected function _loadData() {
		throw new CM_Exception_NotImplemented();
	}

	protected function _onDeleteBefore() {
	}

	protected function _onDelete() {
	}

	protected function _onDeleteAfter() {
	}

	protected function _onChange() {
	}

	protected function _onCreate() {
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
	 * @return CM_Model_Schema_Definition
	 */
	protected function _getSchema() {
		return new CM_Model_Schema_Definition(array());
	}

	/**
	 * @param array|null $data
	 * @return array
	 * @throws CM_Exception_Invalid
	 */
	protected function _getSchemaData($data = null) {
		if (null === $data) {
			$data = $this->_getData();
		}
		$schema = $this->_getSchema();
		if ($schema->isEmpty()) {
			throw new CM_Exception_Invalid('Cannot get schema-data with an empty schema');
		}
		return array_intersect_key($data, array_flip($schema->getFieldNames()));
	}

	/**
	 * @param array $data
	 */
	protected function _validateFields(array $data) {
		$schema = $this->_getSchema();
		foreach ($data as $key => $value) {
			$schema->validateField($key, $value);
		}
	}

	/**
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	protected function _getCache() {
		return self::_getStorageAdapter(static::getCacheClass());
	}

	/**
	 * @return CM_Model_StorageAdapter_AbstractAdapter|null
	 */
	protected function _getPersistence() {
		return self::_getStorageAdapter(static::getPersistenceClass());
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
	 * @param array $idRaw
	 * @return array
	 */
	final protected static function _castIdRaw(array $idRaw) {
		return array_map(function ($el) {
			return (string) $el;
		}, $idRaw);
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
	 * @param int          $type
	 * @param array        $id
	 * @param array|null   $data
	 * @param boolean|null $dataFromPersistence
	 * @return CM_Model_Abstract
	 */
	final public static function factoryGeneric($type, array $id, array $data = null, $dataFromPersistence = null) {
		$className = self::_getClassName($type);
		/*
		 * Cannot use __construct(), since signature is unknown.
		 * unserialize() is ~10% slower.
		 */
		$serialized = serialize(array($id, $data));
		/** @var CM_Model_Abstract $model */
		$model = unserialize('C:' . strlen($className) . ':"' . $className . '":' . strlen($serialized) . ':{' . $serialized . '}');
		if ((null !== $data) && $dataFromPersistence && $cache = $model->_getCache()) {
			$model->_loadAssets(true);
			$cache->save($model->getType(), $model->getIdRaw(), $model->_getData());
		}
		return $model;
	}

	/**
	 * @param array    $idTypeList [['type' => int, 'id' => int|array],...] | [int|array,...] Pass an array of ids if $modelType is used
	 * @param int|null $modelType
	 * @return CM_Model_Abstract[] Can contain null-entries when model doesn't exist
	 * @throws CM_Exception_Invalid
	 */
	public static function factoryGenericMultiple(array $idTypeList, $modelType = null) {
		$modelType = (null !== $modelType) ? (int) $modelType : null;
		$modelList = array();
		$idTypeMap = array();
		$storageTypeList = array(
			'cache'       => array(),
			'persistence' => array()
		);
		$noPersistenceList = array();

		foreach ($idTypeList as $idType) {
			if (null === $modelType) {
				if (!is_array($idType)) {
					throw new CM_Exception_Invalid('`idType` should be an array if `modelType` is not defined: `' . CM_Util::var_line($idType) . '`');
				}
				$type = (int) $idType['type'];
				$id = $idType['id'];
			} else {
				$type = $modelType;
				$id = $idType;
			}
			if (!is_array($id)) {
				$id = array('id' => $id);
			}
			$id = self::_castIdRaw($id);
			$idType = array('type' => $type, 'id' => $id);

			$serializedKey = serialize($idType);
			$modelList[$serializedKey] = null;
			$idTypeMap[$serializedKey] = $idType;

			/** @var CM_Model_Abstract $modelClass */
			$modelClass = CM_Model_Abstract::_getClassName($type);
			if ($cacheStorageClass = $modelClass::getCacheClass()) {
				$storageTypeList['cache'][$cacheStorageClass][$serializedKey] = $idType;
			}
			if ($persistenceStorageClass = $modelClass::getPersistenceClass()) {
				$storageTypeList['persistence'][$persistenceStorageClass][$serializedKey] = $idType;
			} else {
				$noPersistenceList[$serializedKey] = $idType;
			}
		}

		foreach ($storageTypeList as $storageType => $adapterTypeList) {
			$searchItemList = array_filter($modelList, function ($value) {
				return null === $value;
			});
			foreach ($adapterTypeList as $adapterClass => $adapterItemList) {
				/** @var CM_Model_StorageAdapter_AbstractAdapter $storageAdapter */
				$storageAdapter = new $adapterClass();
				$result = $storageAdapter->loadMultiple(array_intersect_key($adapterItemList, $searchItemList));
				foreach ($result as $serializedKey => $modelData) {
					$model = null;
					if (null !== $modelData) {
						$dataFromPersistence = 'persistence' === $storageType;
						$model = self::factoryGeneric($idTypeMap[$serializedKey]['type'], $idTypeMap[$serializedKey]['id'], $modelData,
							$dataFromPersistence);
					}
					$modelList[$serializedKey] = $model;
				}
			}
		}

		// no persistence
		foreach ($noPersistenceList as $serializedKey => $idType) {
			if (!isset($modelList[$serializedKey])) {
				try {
					$model = self::factoryGeneric($idType['type'], $idType['id']);
				} catch (CM_Exception_Nonexistent $ex) {
					$model = null;
				}
				$modelList[$serializedKey] = $model;
			}
		}
		return array_values($modelList);
	}

	public function toArray() {
		$id = $this->_getId();
		$array = array('_type' => $this->getType(), '_id' => $id);
		if (array_key_exists('id', $id)) {
			$array['id'] = (int) $id['id'];
		}
		return $array;
	}

	public static function fromArray(array $data) {
		return self::factoryGeneric($data['_type'], $data['_id']);
	}
}
