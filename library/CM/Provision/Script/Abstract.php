<?php

abstract class CM_Provision_Script_Abstract implements CM_Provision_Script_LoadableInterface {

    /**
     * @return string
     */
    public function getName() {
        return get_class($this);
    }

    /**
     * @return int
     */
    public function getRunLevel() {
        return 5;
    }
}
