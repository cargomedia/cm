<?php

trait CM_Provision_Script_IsLoadedTrait {

    /**
     * @return bool
     */
    abstract protected function _isLoaded();

    public function shouldBeLoaded() {
        return !$this->_isLoaded();
    }

    public function shouldBeUnloaded() {
        return $this->_isLoaded();
    }

}
