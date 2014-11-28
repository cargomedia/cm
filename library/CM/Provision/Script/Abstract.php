<?php

abstract class CM_Provision_Script_Abstract extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    /**
     * @param CM_OutputStream_Interface $output
     * @return
     */
    abstract public function load(CM_OutputStream_Interface $output);

    /**
     * @return bool
     */
    abstract public function shouldBeLoaded();

    /**
     * @param CM_OutputStream_Interface $output
     * @throws CM_Exception_Invalid
     */
    public function reload(CM_OutputStream_Interface $output) {
        if (!$this instanceof CM_Provision_Script_UnloadableInterface) {
            throw new CM_Exception_Invalid('Can only reload unloadable scripts');
        }
        /** @var $this CM_Provision_Script_Abstract|CM_Provision_Script_UnloadableInterface */
        $this->unload($output);
        $this->load($output);
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
