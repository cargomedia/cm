<?php

abstract class CM_Setup_Script_Abstract {

    abstract public function load(CM_Service_Manager $manager);

    /**
     * @return string
     */
    public function getName() {
        return get_class($this);
    }

    /**
     * @return int|null
     */
    public function getOrder() {
        return null;
    }

    /**
     * @return string
     */
    public function getNamespace() {
        return CM_Util::getNamespace(get_class($this));
    }
}
