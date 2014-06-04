<?php

class CM_Config_Node {

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
                $object->$key = $value;
            }
        }
        return $object;
    }

    /**
     * @param string $filenameRelative
     * @throws CM_Exception_Invalid
     */
    public function extend($filenameRelative) {
        foreach (CM_Util::getResourceFiles('config/' . $filenameRelative) as $configFile) {
            $configSetter = require $configFile->getPath();
            if (!$configSetter instanceof Closure) {
                throw new CM_Exception_Invalid('Invalid config file. `' . $configFile->getPath() . '` must return closure');
            }
            $configSetter($this);
        }
    }
}
