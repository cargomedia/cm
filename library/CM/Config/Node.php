<?php

class CM_Config_Node {

    /**
     * @param string $name
     * @return CM_Config_Node
     */
    public function __get($name) {
        return $this->$name = new self();
    }

    /**
     * @return stdClass
     */
    public function export() {
        $object = new stdClass();
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof self) {
                $object->$key = $value->export();
            } else {
                if (is_array($value)) {
                    $value = $this->_evaluateConstantsInArray($value);
                }
                $object->$key = $value;
            }
        }
        return $object;
    }

    /**
     * @param string                    $baseKey
     * @param CM_Config_Node|array|null $property
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function exportAsString($baseKey, $property = null) {
        $baseKey = (string) $baseKey;
        $property = ($property !== null) ? $property : $this;
        $output = '';
        if ($property instanceof self) {
            $getFullKey = function ($base, $key) {
                return $base . '->' . $key;
            };
        } elseif (is_array($property)) {
            $getFullKey = function ($base, $key) {
                if (!(is_string($key) && (null !== $this->_evaluateClassConstant($key)))) {
                    $key = var_export($key, true);
                }
                return $base . '[' . $key . ']';
            };
        } else {
            throw new CM_Exception_Invalid('Invalid property type', null, ['property' => $property]);
        }
        foreach ($property as $key => $value) {
            if (is_array($value)) {
                $output .= $getFullKey($baseKey, $key) . " = [];" . PHP_EOL;
            }
            if (!is_scalar($value)) {
                $output .= $this->exportAsString($getFullKey($baseKey, $key), $value);
            } else {
                if (preg_match('/^(\w+)::class$/', $value, $matches) && class_exists($matches[1])) {
                    $configValue = $value;
                } else {
                    $configValue = var_export($value, true);
                }
                $output .= $getFullKey($baseKey, $key) . " = " . $configValue . ";" . PHP_EOL;
            }
        }
        return $output;
    }

    /**
     * @param string $configBasename
     * @throws CM_Exception_Invalid
     */
    public function extend($configBasename) {
        foreach (CM_Util::getResourceFiles('config/' . $configBasename) as $configFile) {
            $this->extendWithFile($configFile);
        }
    }

    /**
     * @param CM_Config_Node|stdClass $config
     * @param CM_Config_Node|null     $base
     * @return CM_Config_Node
     */
    public function extendWithConfig($config, $base = null) {
        $base = $base ?: $this;

        foreach ($config as $key => $value) {
            if (is_object($value) && isset ($base->$key) && is_object($base->$key)) {
                $base->$key = $this->extendWithConfig($value, $base->$key);
            } elseif (is_array($value) && isset ($base->$key) && is_array($base->$key)) {
                $base->$key = $value + $base->$key;
            } else {
                $base->$key = $value;
            }
        }

        return $base;
    }

    /**
     * @param CM_File $configFile
     * @throws CM_Exception_Invalid
     */
    public function extendWithFile(CM_File $configFile) {
        $configSetter = require $configFile->getPath();
        if (!$configSetter instanceof Closure) {
            throw new CM_Exception_Invalid('Invalid config file. Path must return closure', null, ['path' => $configFile->getPath()]);
        }
        $configSetter($this);
    }

    /**
     * @param array $list
     * @return array
     */
    private function _evaluateConstantsInKeys(array $list) {
        $keys = array_keys($list);
        $keys = \Functional\map($keys, function ($key) {
            if (null !== ($value = $this->_evaluateClassConstant($key))) {
                return $value;
            }
            return $key;
        });
        return array_combine($keys, array_values($list));
    }

    /**
     * @param array $list
     * @return array
     */
    private function _evaluateConstantsInArray(array $list) {
        $constantEvaluator = function ($key) {
            if (is_scalar($key) && null !== ($value = $this->_evaluateClassConstant($key))) {
                return $value;
            }
            return $key;
        };
        $keys = \Functional\map(array_keys($list), $constantEvaluator);
        $values = \Functional\map(array_values($list), $constantEvaluator);
        return array_combine($keys, $values);
    }

    /**
     * @param string $reference
     * @return mixed|null
     */
    private function _evaluateClassConstant($reference) {
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*::[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $reference)) {
            return @constant($reference);
        }
        return null;
    }
}
