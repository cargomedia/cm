<?php

abstract class CM_Provision_Script_Abstract extends CM_Class_Abstract implements CM_Provision_Script_LoadableInterface {

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

    public function reload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        if (!$this instanceof CM_Provision_Script_UnloadableInterface) {
            throw new CM_Exception_Invalid('Can only reload unloadable scripts');
        }
        /** @var $this CM_Provision_Script_Abstract|CM_Provision_Script_UnloadableInterface */
        if (!$this->shouldBeLoaded($manager)) {
            $this->unload($manager, $output);
        }
        $this->load($manager, $output);
    }
}
