<?php

use CM\Transactions\Transaction;

abstract class CM_Model_Abstract extends CM_Class_Abstract
    implements CM_Comparable, CM_ArrayConvertible, JsonSerializable, CM_Cacheable, Serializable, CM_Typed, CM_Debug_DebugInfoInterface {

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
            $this->_setData($data);
        }
        foreach ($this->_getAssets() as $asset) {
            $this->_assets = array_merge($this->_assets, array_fill_keys($asset->getClassHierarchy(), $asset));
        }
        $this->_getData(); // Make sure data can be loaded
    }

    /**
     * @param bool|null $useReplace
     * @throws CM_Exception_Invalid
     * @throws Exception
     */
    public function commit($useReplace = null) {
        $useReplace = (boolean) $useReplace;

        $persistence = $this->_getPersistence();
        if (!$persistence) {
            throw new CM_Exception_Invalid('Cannot create model without persistence');
        }

        $type = $this->getType();
        $dataSchema = $this->_getSchemaData();
        if ($this->hasIdRaw()) {
            if (!empty($dataSchema)) {
                $persistence->save($type, $this->getIdRaw(), $dataSchema);
            }
            if ($cache = $this->_getCache()) {
                $cache->save($type, $this->getIdRaw(), $this->_getData());
            }
            $this->_onChange();
        } else {
            $transaction = new Transaction();
            try {
                $this->_validateFields($this->_getData(), true);
                if ($useReplace) {
                    if (!$persistence instanceof CM_Model_StorageAdapter_ReplaceableInterface) {
                        $adapterName = get_class($persistence);
                        throw new CM_Exception_NotImplemented('Param `useReplace` is not allowed with adapter', null, ['adapterName' => $adapterName]);
                    }
                    $idRaw = $persistence->replace($type, $dataSchema);
                } else {
                    $idRaw = $persistence->create($type, $dataSchema);
                    $transaction->addRollback(function () use ($persistence, $type, $idRaw) {
                        $persistence->delete($type, $idRaw);
                    });
                }

                $this->_id = self::_castIdRaw($idRaw);

                if ($cache = $this->_getCache()) {
                    $this->_loadAssets(true);
                    $cache->save($type, $this->getIdRaw(), $this->_getData());
                    $transaction->addRollback(function () use ($cache, $type) {
                        $cache->delete($type, $this->getIdRaw());
                    });
                }
                $this->_changeContainingCacheables();
                $this->_onCreate();
                if ($useReplace) {
                    $this->_onChange();
                }
            } catch (Exception $e) {
                $transaction->rollback();
                throw $e;
            }
        }
        $this->_autoCommit = true;
    }


    public function delete() {
        $containingCacheables = $this->_getContainingCacheables();
        $this->_onDeleteBefore();
        foreach ($this->_assets as $asset) {
            $asset->_onModelDelete();
        }
        $this->_onDelete();
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
     * @return int
     */
    public function getId() {
        return (int) $this->_getIdKey('id');
    }

    /**
     * @return bool
     */
    public function hasId() {
        return $this->hasIdRaw() && $this->_hasIdKey('id');
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
    public function hasIdRaw() {
        return (null !== $this->_id);
    }

    /**
     * @return CM_ModelAsset_Abstract[]
     */
    public function getAssets() {
        return $this->_assets;
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
        return (get_class($this) == get_class($model) && $this->getIdRaw() === $model->getIdRaw());
    }

    /**
     * @param bool $state
     */
    public function setAutoCommit($state) {
        $this->_autoCommit = (bool) $state;
    }

    final public function serialize() {
        return serialize(array('id' => $this->getIdRaw()));
    }

    final public function unserialize($serialized) {
        $unserialized = unserialize($serialized);
        $id = $unserialized['id'];
        $data = null;
        if (isset($unserialized['data'])) {
            $data = $unserialized['data'];
        }
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
            throw new CM_Exception('Model has no field', null, ['field' => $field]);
        }
        if (!array_key_exists($field, $this->_dataDecoded)) {
            if ($schema = $this->_getSchema()) {
                $this->_dataDecoded[$field] = $schema->decodeField($field, $data[$field]);
            } else {
                $this->_dataDecoded[$field] = $data[$field];
            }
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
            $data[$key] = $schema ? $schema->encodeField($key, $value) : $value;
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
                if (!$schema) {
                    throw new CM_Exception_Invalid('Cannot save to persistence without a schema');
                }
                if ($schema->hasField(array_keys($data))) {
                    $persistence->save($this->getType(), $this->getIdRaw(), $this->_getSchemaData());
                }
            }
            $this->_onChange();
        }
    }

    public function getDebugInfo() {
        $debugInfo = get_class($this);
        if ($this->hasIdRaw()) {
            $debugInfo .= '(' . implode(', ', (array) $this->getIdRaw()) . ')';
        }
        return $debugInfo;
    }

    /**
     * @return array
     * @throws CM_Exception_Nonexistent
     */
    protected function _getData() {
        if (null === $this->_data) {
            if ($cache = $this->_getCache()) {
                if (false !== ($data = $cache->load($this->getType(), $this->getIdRaw()))) {
                    $this->_setData($data);
                }
            }
            if (null === $this->_data) {
                if ($persistence = $this->_getPersistence()) {
                    if (false !== ($data = $persistence->load($this->getType(), $this->getIdRaw()))) {
                        $this->_setData($data);
                    }
                } else {
                    if (is_array($data = $this->_loadData())) {
                        $this->_setData($data);
                    }
                }
                if (null === $this->_data) {
                    throw new CM_Exception_Nonexistent('Model has no data', null, [
                        'className' => get_called_class(),
                        'rawId'     => CM_Util::var_line($this->getIdRaw()),
                    ]);
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
        $this->_validateFields($data);
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
        if ($persistence = $this->_getPersistence()) {
            $persistence->delete($this->getType(), $this->getIdRaw());
        }
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
     * @param string $key
     * @return array|mixed
     * @throws CM_Exception_Invalid
     */
    final protected function _getIdKey($key) {
        $key = (string) $key;
        $idRaw = $this->getIdRaw();
        if (!$this->_hasIdKey($key)) {
            throw new CM_Exception_Invalid('Id-array has no field.', null, ['key' => $key]);
        }
        return $idRaw[$key];
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function _hasIdKey($key) {
        $key = (string) $key;
        $idRaw = $this->getIdRaw();
        return array_key_exists($key, $idRaw);
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
            throw new CM_Exception('No such asset', null, ['assetClassName' => $className]);
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

    protected function _changeContainingCacheables() {
        foreach ($this->_getContainingCacheables() as $cacheable) {
            $cacheable->_change();
        }
    }

    /**
     * @return CM_Model_Schema_Definition|null
     */
    protected function _getSchema() {
        return null;
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
        if (!$schema = $this->_getSchema()) {
            throw new CM_Exception_Invalid('Cannot get schema-data without a schema');
        }
        return array_intersect_key($data, array_flip($schema->getFieldNames()));
    }

    /**
     * @param array     $data
     * @param bool|null $checkMissingFields
     */
    protected function _validateFields(array $data, $checkMissingFields = null) {
        if ($schema = $this->_getSchema()) {
            if ($checkMissingFields) {
                foreach ($schema->getFieldNames() as $key) {
                    $value = isset($data[$key]) ? $data[$key] : null;
                    $schema->validateField($key, $value);
                }
            } else {
                foreach ($data as $key => $value) {
                    $schema->validateField($key, $value);
                }
            }
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
     * @throws Exception
     */
    final public static function createStatic(array $data = null) {
        $transaction = new Transaction();
        try {
            if ($data === null) {
                $data = [];
            }
            $model = static::_createStatic($data);
            $transaction->addRollback(function() use ($model) {
                $model->delete();
            });
            $model->_changeContainingCacheables();
            $model->_onCreate();
            return $model;
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
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
     * @return string
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
     * @return string
     */
    public static function getTableName() {
        return strtolower(get_called_class());
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
            throw new CM_Exception_Invalid('Invalid storage adapter class', null, ['storageAdapterClass' => $className]);
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
        $serialized = serialize(array('id' => $id, 'data' => $data));
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
        $serializedKeyMap = array();
        $storageTypeList = array(
            'cache'       => array(),
            'persistence' => array()
        );
        $noPersistenceList = array();

        foreach ($idTypeList as $originalKey => $idType) {
            if (null === $modelType) {
                if (!is_array($idType)) {
                    throw new CM_Exception_Invalid('`idType` should be an array if `modelType` is not defined', null, ['idType' => CM_Util::var_line($idType)]);
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
            $serializedKeyMap[$originalKey] = $serializedKey;
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
        $resultList = array();
        foreach ($serializedKeyMap as $originalKey => $serializedKey) {
            $resultList[] = $modelList[$serializedKeyMap[$originalKey]];
        }
        return $resultList;
    }

    public function toArray() {
        return array('_type' => $this->getType(), '_id' => $this->getIdRaw());
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
        ];
    }

    public static function fromArray(array $data) {
        return self::factoryGeneric($data['_type'], $data['_id']);
    }
}
