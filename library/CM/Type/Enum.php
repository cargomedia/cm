<?php

abstract class CM_Type_Enum implements JsonSerializable {

    /** @var string */
    private $_currentValue;

    /** @var array */
    protected static $_constantsCache = [];

    /**
     * @param string|null $value
     * @throws CM_Exception_Invalid
     */
    public function __construct($value = null) {
        if (null === $value) {
            $defaultValue = (string) $this->_getDefaultValue();
            if (!$this->_isValidValue($defaultValue)) {
                throw new CM_Exception_Invalid('Invalid default value for enum class', null, ['className' => get_class($this)]);
            }
            $this->_currentValue = $defaultValue;
        } else {
            $value = (string) $value;
            if (!$this->_isValidValue($value)) {
                throw new CM_Exception_Invalid('Invalid value for enum class', null, [
                    'value'     => $value,
                    'className' => get_class($this),
                ]);
            }
            $this->_currentValue = $value;
        }
    }

    function jsonSerialize() {
        return [
            'value' => (string) $this,
        ];
    }

    final public function __toString() {
        return (string) $this->_currentValue;
    }

    /**
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _getDefaultValue() {
        throw new CM_Exception_Invalid('Default value in not defined for enum class', null, ['className' => get_class($this)]);
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function _isValidValue($value) {
        $value = (string) $value;
        return in_array($value, static::getConstantList(), true);
    }

    /**
     * @return array
     */
    public static function getConstantList() {
        $class = get_called_class();
        if (!array_key_exists($class, static::$_constantsCache)) {
            $reflection = new ReflectionClass($class);
            static::$_constantsCache[$class] = $reflection->getConstants();
        }
        return static::$_constantsCache[$class];
    }
}
