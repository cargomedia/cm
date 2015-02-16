<?php

/**
 * @deprecated
 * Use CM_Class_ConfigurableTrait if you want to use class configuration
 * Use CM_Class_TypedTrait if you want to introduce a new type-namespace
 * Use CM_Util if you want to use class hierarchy helper functions
 */
abstract class CM_Class_Abstract {

    use CM_Class_TypedTrait;

    /**
     * @return string[] List of class names
     */
    public function getClassHierarchy() {
        $className = get_called_class();
        return CM_Util::getClassHierarchy($className);
    }

    /**
     * @throws CM_Exception_Invalid
     * @return string
     */
    protected static function _getClassNamespace() {
        return CM_Util::getNamespace(get_called_class());
    }

    /**
     * @param boolean|null $includeAbstracts
     * @return string[]
     */
    public static function getClassChildren($includeAbstracts = null) {
        $className = get_called_class();
        return CM_Util::getClassChildren($className, $includeAbstracts);
    }
}
