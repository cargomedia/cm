<?php

trait CM_Provision_Script_IsLoadedTrait {

    /**
     * @param CM_Service_Manager $manager
     * @return bool
     */
    abstract protected function _isLoaded(CM_Service_Manager $manager);

    public function shouldBeLoaded(CM_Service_Manager $manager) {
        return !$this->_isLoaded($manager);
    }

    public function shouldBeUnloaded(CM_Service_Manager $manager) {
        return $this->_isLoaded($manager);
    }

}
