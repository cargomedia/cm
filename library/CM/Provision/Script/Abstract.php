<?php

abstract class CM_Provision_Script_Abstract implements CM_Provision_Script_LoadableInterface {

    /**
     * @return string
     */
    public function getName() {
        return get_class($this);
    }

    /**
     * @param CM_Service_Manager $manager
     * @return bool
     */
    public function isLoaded(CM_Service_Manager $manager) {
        return false;
    }

    /**
     * @return int
     */
    public function getRunLevel() {
        return 5;
    }
}
