<?php

class Migration_1497600643_Sitesettingsindex implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        $mongo = $this->getServiceManager()->getMongoDb();
        if ($mongo->existsCollection('cm_site')) {
            $mongo->createIndex('cm_site', ['_type' => 1], ['unique' => true]);
            $mongo->createIndex('cm_site', ['default' => 1], ['unique' => true, 'sparse' => true]);
        }
    }
}
