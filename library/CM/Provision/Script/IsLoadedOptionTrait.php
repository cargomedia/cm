<?php

trait CM_Provision_Script_IsLoadedOptionTrait {

    use CM_Provision_Script_IsLoadedTrait;

    /**
     * @param bool $loaded
     */
    protected function _setLoaded($loaded) {
        CM_Option::getInstance()->set($this->_getOptionName(), (bool) $loaded);
    }

    /**
     * @param CM_Service_Manager $manager
     * @return bool
     */
    protected function _isLoaded(CM_Service_Manager $manager) {
        try {
            return CM_Option::getInstance()->get($this->_getOptionName());
        } catch (CM_Db_Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    private function _getOptionName() {
        return 'SetupScript.' . get_class($this);
    }
}
