<?php

trait CM_Class_TypeTrait {

    use CM_Class_ConfigTrait;

    /**
     * @return int
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    public function getType() {
        return static::getTypeStatic();
    }

    /**
     * @return int
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    public static function getTypeStatic() {
        if (!isset(self::_getConfig()->type)) {
            throw new CM_Class_Exception_TypeNotConfiguredException('Class `' . get_called_class() . '` has no type configured.');
        }
        return self::_getConfig()->type;
    }

    /**
     * @param int $type
     * @return string
     * @throws CM_Class_Exception_TypeNotConfiguredException
     */
    protected static function _getClassName($type = null) {
        $config = self::_getConfig();
        if (null !== $type) {
            $type = (int) $type;
            if (empty($config->types[$type])) {
                throw new CM_Class_Exception_TypeNotConfiguredException("Type `{$type}` not configured for class `" . get_called_class() . '`.');
            }
            return $config->types[$type];
        }
        if (!empty($config->class)) {
            return $config->class;
        }
        return get_called_class();
    }
}
