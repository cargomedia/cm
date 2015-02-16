<?php

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
