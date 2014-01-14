<?php

class CM_Config_Generator extends CM_Class_Abstract {

	private $_typesMaxValue = 0;

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
	 * @return array[]
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
		if (isset(CM_Config::get()->CM_Class_Abstract->typesMaxValue)) {
			$this->_typesMaxValue = CM_Config::get()->CM_Class_Abstract->typesMaxValue;
		}
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
			$this->_namespaceTypes[$namespaceClass] = array();
			$containedClasses = $namespaceClass::getClassChildren();
			$reflectionClass = new ReflectionClass($namespaceClass);
			if (!$reflectionClass->isAbstract()) {
				array_unshift($containedClasses, $namespaceClass);
			}
			foreach ($containedClasses as $class) {
				if (false === $type = array_search($class, $this->_classTypes)) {
					$type = ++$this->_typesMaxValue;
					$this->_classTypes[$type] = $class;
				}
				$this->_namespaceTypes[$namespaceClass][$type] = $class;
			}
		}
	}

	/**
	 * @return string
	 */
	public function generateConfigClassTypes() {
		if (empty($this->_classTypes)) {
			$this->generateClassTypes();
		}
		$output = '';
		foreach ($this->getNamespaceTypes() as $namespaceClass => $typeList) {
			ksort($typeList);
			$output .= '$config->' . $namespaceClass . '->types = array();' . PHP_EOL;
			foreach ($typeList as $type => $class) {
				$output .= '$config->' . $namespaceClass . '->types[' . $type . '] = \'' . $class . '\';' . PHP_EOL;
			}
			$output .= PHP_EOL;
		}
		$classTypes = $this->getClassTypes();
		ksort($classTypes);
		$output .= PHP_EOL;
		foreach ($classTypes as $type => $class) {
			$output .= '$config->' . $class . '->type = ' . $type . ';' . PHP_EOL;
		}
		$output .= PHP_EOL . '$config->CM_Class_Abstract->typesMaxValue = ' . $this->_typesMaxValue . ';' . PHP_EOL;
		return $output;
	}

	public function generateConfigActionVerbs() {
		$maxValue = 0;
		if (isset(CM_Config::get()->CM_Action_Abstract->verbsMaxValue)) {
			$maxValue = CM_Config::get()->CM_Action_Abstract->verbsMaxValue;
		}

		$currentVerbs = array();
		if (isset(CM_Config::get()->CM_Action_Abstract->verbs)) {
			$currentVerbs = CM_Config::get()->CM_Action_Abstract->verbs;
		}

		$content = '$config->CM_Action_Abstract->verbs = array();' . PHP_EOL;
		foreach ($this->getActionVerbs() as $actionVerb) {
			if (!array_key_exists($actionVerb['value'], $currentVerbs)) {
				$maxValue++;
				$currentVerbs[$actionVerb['value']] = $maxValue;
			}
			$key = $actionVerb['className'] . '::' . $actionVerb['name'];
			$id = $currentVerbs[$actionVerb['value']];
			$content .= '$config->CM_Action_Abstract->verbs[' . $key . '] = ' . var_export($id, true) . ';' . PHP_EOL;
		}
		$content .= '$config->CM_Action_Abstract->verbsMaxValue = ' . $maxValue . ';' . PHP_EOL;
		return $content;
	}

	/**
	 *
	 * @throws CM_Exception_Invalid
	 * @return array
	 */
	public function getActionVerbs() {
		$actionVerbs = array();
		$actionVerbsValues = array();
		$classNames = CM_Action_Abstract::getClassChildren(true);
		array_unshift($classNames, 'CM_Action_Abstract');
		foreach ($classNames as $className) {
			$class = new ReflectionClass($className);
			$constants = $class->getConstants();
			unset($constants['TYPE']);
			foreach ($constants as $constant => $value) {
				if (array_key_exists($constant, $actionVerbsValues) && $actionVerbsValues[$constant] !== $value) {
					throw new CM_Exception_Invalid(
						'Constant `' . $className . '::' . $constant . '` already set. Tried to set value to `' . $value . '` - previously set to `' .
						$actionVerbsValues[$constant] . '`.');
				}
				if (!array_key_exists($constant, $actionVerbsValues) && in_array($value, $actionVerbsValues)) {
					throw new CM_Exception_Invalid(
						'Cannot set `' . $className . '::' . $constant . '` to `' . $value . '`. This value is already used for `' . $className .
						'::' . array_search($value, $actionVerbsValues) . '`.');
				}
				if (!array_key_exists($constant, $actionVerbsValues)) {
					$actionVerbsValues[$constant] = $value;
					$actionVerbs[] = array('name' => $constant, 'value' => $value, 'className' => $className,);
				}
			}
		}
		return $actionVerbs;
	}

	/**
	 * @return CM_Config_Mapping
	 */
	protected function _getMapping() {
		return new CM_Config_Mapping();
	}
}
