<?php

abstract class CM_Provision_Script_Abstract {

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     */
    abstract public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output);

    /**
     * @return string
     */
    public function getName() {
        return get_class($this);
    }

    /**
     * @return bool
     */
    public function isLoaded() {
        return false;
    }

    /**
     * @return int
     */
    public function getRunLevel() {
        return 5;
    }
}
