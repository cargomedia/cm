<?php

abstract class CM_Provision_Script_Abstract {

    /**
     * @param CM_Service_Manager $manager
     */
    abstract public function load(CM_Service_Manager $manager);

    /**
     * @return string
     */
    public function getName() {
        return get_class($this);
    }
}
