<?php

class CM_Config_Generator extends CM_Class_Abstract {

	/** @var string[] */
	private $_classTypes = array();

	/** @var string[][] */
	private $_namespaceTypes = array();

	/**
	 * @param CM_File $source
	 * @return string
	 */
	public function generateMappedOutput(CM_File $source) {
		$entryList = CM_Params::decode($source->read(), true);
		$output = '<?php' . PHP_EOL;
		$mapping = $this->_getMapping();
		foreach ($entryList as $key => $value) {
			if (is_array($value)) {
				$keyMapped = $mapping->getConfigKey($key);
				foreach ($value as $subKey => $subValue) {
					$output .= '$config->' . $keyMapped . '->' . $subKey . ' = ' . var_export($subValue, true) . ';' . PHP_EOL;
				}
			} else {
				$output .= '$config->' . $key . ' = ' . var_export($value, true) . ';' . PHP_EOL;
			}
		}
		return $output;
	}

	/**
	 * @throws CM_Exception_Invalid
	 * @return string[][]
	 */
	public function getNamespaceTypes() {
		if (empty($this->_namespaceTypes)) {
			throw new CM_Exception_Invalid('Class Types have not been generated.');
		}
		return $this->_namespaceTypes;
	}

	/**
	 * @return string[]
	 * @throws CM_Exception_Invalid
	 */
	public function getClassTypes() {
		if (empty($this->_classTypes)) {
			throw new CM_Exception_Invalid('Class Types have not been generated.');
		}
		return $this->_classTypes;
	}

	public function generateClassTypes() {
		$config = CM_Config::get();
		$valueCurrent = 1;
		$typedClasses = CM_Util::getClassChildren('CM_Typed', true);
		/** @var CM_Class_Abstract[] $namespaceClassList */
		$namespaceClassList = array();
		// fetch type-namespaces
		foreach ($typedClasses as $class) {
			$isHighestTypedClass = true;
			foreach (class_parents($class) as $parentClass) {
				if (is_subclass_of($parentClass, 'CM_Typed')) {
					$isHighestTypedClass = false;
				}
			}
			if ($isHighestTypedClass) {
				$namespaceClassList[] = $class;
			}
		}
		// fetch current types
		foreach ($namespaceClassList as $namespaceClass) {
			if (isset($config->$namespaceClass->types)) {
				foreach ($config->$namespaceClass->types as $type => $class) {
					if ($classNameDuplicate = array_search($type, $this->_classTypes)) {
						throw new CM_Exception_Invalid(
							'Duplicate `TYPE` constant for `' . $class . '` and `' . $classNameDuplicate . '`. Both equal `' . $type . '`.');
					}
					$this->_classTypes[$type] = $class;
				}
			}
		}
		// generate new types
		foreach ($namespaceClassList as $namespaceClass) {
			foreach ($namespaceClass::getClassChildren() as $class) {
				if (false === $type = array_search($class, $this->_classTypes)) {
					while (isset($this->_classTypes[$valueCurrent])) {
						$valueCurrent++;
					}
					$type = $valueCurrent;
					$this->_classTypes[$type] = $class;
				}
				$this->_namespaceTypes[$namespaceClass][$type] = $class;
			}
		}
	}

	/**
	 * @return CM_Config_Mapping
	 */
	protected function _getMapping() {
		return new CM_Config_Mapping();
	}
}
