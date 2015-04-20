<?php

class CM_Config_Generator extends CM_Class_Abstract {

    /** @var int  */
    private $_typesMaxValue = 0;

    /** @var string[] */
    private $_classTypes = array();

    /** @var string[] */
    private $_classTypesAdded = array();

    /** @var string[] */
    private $_classTypesRemoved = array();

    /** @var string[][] */
    private $_namespaceTypes = array();

    /**
     * @return string[][]
     */
    public function getNamespaceTypes() {
        $this->_checkTypesGenerated();
        return $this->_namespaceTypes;
    }

    /**
     * @return string[]
     */
    public function getClassTypes() {
        $this->_checkTypesGenerated();
        return $this->_classTypes;
    }

    /**
     * @return string[]
     */
    public function getClassTypesAdded() {
        $this->_checkTypesGenerated();
        return $this->_classTypesAdded;
    }

    /**
     * @return string[]
     */
    public function getClassTypesRemoved() {
        $this->_checkTypesGenerated();
        return $this->_classTypesRemoved;
    }

    /**
     * @return CM_Config_Node
     */
    public function getConfigClassTypes() {
        $this->generateClassTypes();
        $config = new CM_Config_Node();
        foreach ($this->getNamespaceTypes() as $namespaceClass => $typeList) {
            ksort($typeList);
            $config->$namespaceClass->types = $typeList;
        }
        $classTypes = $this->getClassTypes();
        ksort($classTypes);
        foreach ($classTypes as $type => $class) {
            $config->$class->type = $type;
        }
        $config->CM_Class_Abstract->typesMaxValue = $this->_typesMaxValue;
        return $config;
    }

    /**
     * @return CM_Config_Node
     * @throws CM_Exception_Invalid
     */
    public function getConfigActionVerbs() {
        $maxValue = 0;
        if (isset(CM_Config::get()->CM_Action_Abstract->verbsMaxValue)) {
            $maxValue = CM_Config::get()->CM_Action_Abstract->verbsMaxValue;
        }

        $currentVerbs = array();
        if (isset(CM_Config::get()->CM_Action_Abstract->verbs)) {
            $currentVerbs = CM_Config::get()->CM_Action_Abstract->verbs;
        }

        $config = new CM_Config_Node();
        $actionVerbs = [];
        foreach ($this->getActionVerbs() as $actionVerb) {
            if (!array_key_exists($actionVerb['value'], $currentVerbs)) {
                $maxValue++;
                $currentVerbs[$actionVerb['value']] = $maxValue;
            }
            $key = $actionVerb['className'] . '::' . $actionVerb['name'];
            $id = $currentVerbs[$actionVerb['value']];
            $actionVerbs[$key] = $id;
        }
        $config->CM_Action_Abstract->verbs = $actionVerbs;
        $config->CM_Action_Abstract->verbsMaxValue = $maxValue;
        return $config;
    }

    public function generateClassTypes() {
        $config = CM_Config::get();
        if (isset(CM_Config::get()->CM_Class_Abstract->typesMaxValue)) {
            $this->_typesMaxValue = CM_Config::get()->CM_Class_Abstract->typesMaxValue;
        }
        $typedClasses = CM_Util::getClassChildren('CM_Class_TypedInterface', true);
        /** @var CM_Class_Abstract[]|string[] $namespaceClassList */
        $namespaceClassList = array();
        // fetch type-namespaces
        foreach ($typedClasses as $class) {
            if (!is_subclass_of(get_parent_class($class), 'CM_Class_TypedInterface')) {
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
                    if (class_exists($class)) {
                        $this->_classTypes[$type] = $class;
                    } else {
                        $this->_classTypesRemoved[] = $class;
                    }
                }
            }
        }
        // generate new types
        foreach ($namespaceClassList as $namespaceClass) {
            $this->_namespaceTypes[$namespaceClass] = array();
            $containedClasses = CM_Util::getClassChildren($namespaceClass);
            $reflectionClass = new ReflectionClass($namespaceClass);
            if (!$reflectionClass->isAbstract()) {
                array_unshift($containedClasses, $namespaceClass);
            }
            foreach ($containedClasses as $class) {
                if (false === $type = array_search($class, $this->_classTypes)) {
                    $type = ++$this->_typesMaxValue;
                    $this->_classTypes[$type] = $class;
                    $this->_classTypesAdded[$type] = $class;
                }
                $this->_namespaceTypes[$namespaceClass][$type] = $class;
            }
        }
    }

    /**
     *
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function getActionVerbs() {
        $actionVerbs = array();
        $actionVerbsValues = array();
        $classNames = CM_Util::getClassChildren('CM_Action_Abstract', true);
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
     * @throws CM_Exception_Invalid
     */
    private function _checkTypesGenerated() {
        if (empty($this->_namespaceTypes)) {
            throw new CM_Exception_Invalid('Class Types have not been generated.');
        }
    }
}
