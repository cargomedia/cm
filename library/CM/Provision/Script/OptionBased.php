<?php

abstract class CM_Provision_Script_OptionBased extends CM_Provision_Script_Abstract implements CM_Typed {

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
        return __CLASS__ . ':' . $this->getType();
    }
}
