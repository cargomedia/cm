<?php

abstract class CM_Provision_Script_Abstract extends CM_Class_Abstract {

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     */
    abstract public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output);

    /**
     * @param CM_Service_Manager $manager
     * @return bool
     */
    abstract public function shouldBeLoaded(CM_Service_Manager $manager);

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     * @throws CM_Exception_Invalid
     */
    public function reload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        if (!$this instanceof CM_Provision_Script_UnloadableInterface) {
            throw new CM_Exception_Invalid('Can only reload unloadable scripts');
        }
        /** @var $this CM_Provision_Script_Abstract|CM_Provision_Script_UnloadableInterface */
        $this->unload($manager, $output);
        $this->load($manager, $output);
    }

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
