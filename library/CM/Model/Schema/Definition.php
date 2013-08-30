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
		return array_key_exists($key, $this->_schema);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return mixed
	 * @throws CM_Exception_Invalid
	 * @throws CM_Model_Exception_Validation
	 */
	public function validateField($key, $value) {
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
							if (!is_int($value) && !(is_string($value) && $value === (string) (int) $value)) {
								throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an integer');
							}
							break;
						case 'float':
							if (!(is_float($value) || is_int($value))
									&& !(is_string($value) && ($value === (string) (float) $value || $value === (string) (int) $value))
							) {
								throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a float');
							}
							break;
						case 'string':
							if (!is_string($value)) {
								throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a string');
							}
							break;
						case 'boolean':
						case 'bool':
							if (!is_bool($value) && !(is_string($value) && ('0' === $value || '1' === $value))) {
								throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a boolean');
							}
							break;
						case 'array':
							if (!is_array($value)) {
								throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an array');
							}
							break;
						default:
							if (class_exists($type) && is_subclass_of($type, 'CM_Model_Abstract')) {
								$value = CM_Params::decode($value, true);
								if (is_array($value)) {
									if (!array_key_exists('id', $value) || !is_int($value['id']) && !(is_string($value['id']) && $value['id'] === (string) (int) $value['id'])) {
										throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid model-id');
									}
								} else {
									if (!is_int($value) && !(is_string($value) && $value === (string) (int) $value)) {
										throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid model-id');
									}
								}
								break;
							}
							throw new CM_Exception_Invalid('Invalid type `' . $type . '`');
					}
				}
			}
		}

		return $value;
	}
}
