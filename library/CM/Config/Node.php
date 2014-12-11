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
                    $this->_evaluateConstantsInKeys($value);
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
                if (!(is_string($key) && $this->_evaluateClassConstant($key))) {
                    $key = var_export($key, true);
                }
                return $base . '[' . $key . ']';
            };
        } else {
            throw new CM_Exception_Invalid('Invalid property type', ['property' => $property]);
        }
        foreach ($property as $key => $value) {
            if (is_array($value)) {
                $output .= $getFullKey($baseKey, $key) . " = [];" . PHP_EOL;
            }
            if (!is_scalar($value)) {
                $output .= $this->exportAsString($getFullKey($baseKey, $key), $value);
            } else {
                $output .= $getFullKey($baseKey, $key) . " = " . var_export($value, true) . ";" . PHP_EOL;
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
     * @param CM_File $configFile
     * @throws CM_Exception_Invalid
     */
    public function extendWithFile(CM_File $configFile) {
        $configSetter = require $configFile->getPath();
        if (!$configSetter instanceof Closure) {
            throw new CM_Exception_Invalid('Invalid config file. `' . $configFile->getPath() . '` must return closure');
        }
        $configSetter($this);
    }

    /**
     * @param array $list
     */
    private function _evaluateConstantsInKeys(array &$list) {
        $keys = array_keys($list);
        $keys = \Functional\map($keys, function ($key) {
            if ($value = $this->_evaluateClassConstant($key)) {
                return $value;
            }
            return $key;
        });
        $list = array_combine($keys, array_values($list));
    }

    /**
     * @param string $reference
     * @return mixed|null
     */
    private function _evaluateClassConstant($reference) {
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*::[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $reference) &&
            $value = eval("return {$reference};")
        ) {
            return $value;
        }
        return null;
    }
}
