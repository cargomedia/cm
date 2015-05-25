<?php

class CM_App_SetupScript_Currency extends CM_Provision_Script_OptionBased {

    public function load(CM_OutputStream_Interface $output) {
        $defaultCurrencySettings = CM_Model_Currency::_getConfig()->default;
        CM_Model_Currency::create($defaultCurrencySettings['code'], $defaultCurrencySettings['abbreviation']);

        $this->_setLoaded(true);
    }

    public function getRunLevel() {
        return 10;
    }
}
