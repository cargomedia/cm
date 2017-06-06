<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @synchronized
     */
    public function start() {
        $maintenance = $this->getServiceManager()->getMaintenance();
        while (true) {
            $maintenance->runEvents();
            sleep(1);
        }
    }

    public static function getPackageName() {
        return 'maintenance';
    }
}
