<?php

class Migration_1486572082_ClockworkJobs implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        $mongo = $this->getServiceManager()->getMongoDb();
        if (!$mongo->existsCollection('cm_clockwork')) {
            $mongo->createCollection('cm_clockwork');
            $mongo->createIndex('cm_clockwork', ['context' => 1], ['unique' => true]);
        }
    }
}
