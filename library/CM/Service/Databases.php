<?php

class CM_Service_Databases extends CM_Service_ManagerAware {

    /**
     * @return CM_Db_Client
     */
    public function getMaster() {
        return $this->getServiceManager()->get('database-master', 'CM_Db_Client');
    }

    /**
     * @return CM_Db_Client
     */
    public function getRead() {
        $serviceManager = $this->getServiceManager();
        if ($serviceManager->has('database-read')) {
            return $serviceManager->get('database-read', 'CM_Db_Client');
        }
        return $this->getMaster();
    }

    /**
     * @return CM_Db_Client
     */
    public function getReadMaintenance() {
        $serviceManager = $this->getServiceManager();
        if ($serviceManager->has('database-read-maintenance')) {
            return $serviceManager->get('database-read-maintenance', 'CM_Db_Client');
        }
        return $this->getRead();
    }
}
