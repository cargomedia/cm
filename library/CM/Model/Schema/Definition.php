<?php

class CM_Model_Schema_Definition {

    /** @var array */
    private $_schema;

    /**
     * @param array $schema
     */
    public function __construct(array $schema) {
        $this->_schema = $schema;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function encodeField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'string':
                            break;
                        case 'boolean':
                        case 'bool':
                            $value = (boolean) $value;
                            break;
                        case 'array':
                            break;
                        case 'DateTime':
                            /** @var DateTime $value */
                            $value = $value->getTimestamp();
                            break;
                        default:
                            if (!(class_exists($type) && is_subclass_of($type, 'CM_Model_Abstract'))) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid model');
                            }
                            if (!$value instanceof $type) {
                                throw new CM_Model_Exception_Validation(
                                    'Value `' . CM_Util::var_line($value) . '` is not an instance of `' . $type . '`');
                            }
                            /** @var CM_Model_Abstract $value */
                            $id = $value->getIdRaw();
                            if (count($id) == 1) {
                                $value = $value->getId();
                            } else {
                                $value = CM_Params::encode($id, true);
                            }
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function decodeField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'string':
                            break;
                        case 'boolean':
                        case 'bool':
                            $value = (boolean) $value;
                            break;
                        case 'array':
                            break;
                        case 'DateTime':
                            $value = DateTime::createFromFormat('U', $value);
                            break;
                        default:
                            $id = CM_Params::decode($value, true);
                            if (!is_array($id)) {
                                $id = array('id' => $id);
                            }
                            $value = CM_Model_Abstract::factoryGeneric($type::getTypeStatic(), $id);
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @return string[]
     */
    public function getFieldNames() {
        return array_keys($this->_schema);
    }

    /**
     * @param string|string[] $key
     * @return bool
     */
    public function hasField($key) {
        if (is_array($key)) {
            return count(array_intersect($key, array_keys($this->_schema))) > 0;
        }
        return isset($this->_schema[$key]);
    }

    /**
     * @return boolean
     */
    public function isEmpty() {
        return empty($this->_schema);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function validateField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            $optional = !empty($schemaField['optional']);

            if (!$optional && null === $value) {
                throw new CM_Model_Exception_Validation('Field `' . $key . '` is mandatory');
            }

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            if (!$this->_isInt($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an integer');
                            }
                            break;
                        case 'float':
                            if (!$this->_isFloat($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a float');
                            }
                            break;
                        case 'string':
                            if (!$this->_isString($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a string');
                            }
                            break;
                        case 'boolean':
                        case 'bool':
                            if (!$this->_isBoolean($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a boolean');
                            }
                            break;
                        case 'array':
                            if (!$this->_isArray($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an array');
                            }
                            break;
                        case 'DateTime':
                            if (!$this->_isInt($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid timestamp');
                            }
                            break;
                        default:
                            if (class_exists($type) && is_subclass_of($type, 'CM_Model_Abstract')) {
                                if (!$this->_isModel($value)) {
                                    throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid model');
                                }
                                break;
                            }
                            throw new CM_Exception_Invalid('Invalid type `' . $type . '`');
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isArray($value) {
        return is_array($value);
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isBoolean($value) {
        return is_bool($value) || (is_string($value) && ('0' === $value || '1' === $value));
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isFloat($value) {
        return is_numeric($value);
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isInt($value) {
        return is_int($value) || (is_string($value) && $value === (string) (int) $value);
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isModel($value) {
        $value = CM_Params::decode($value, true);
        if (is_array($value)) {
            if (!array_key_exists('id', $value)) {
                return false;
            }
            $value = $value['id'];
        }
        if (!$this->_isInt($value)) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    protected function _isString($value) {
        return is_string($value);
    }
}
