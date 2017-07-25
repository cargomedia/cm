<?php

class Migration_1500968768_Languagekey implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        CM_Model_LanguageKey::create('Click again to confirm');
    }
}
