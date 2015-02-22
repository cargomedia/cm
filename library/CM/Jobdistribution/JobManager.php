<?php

class CM_Jobdistribution_JobManager {

    /** @var bool */
    protected $_gearmanEnabled;

    /** @var array */
    protected $_gearmanServers;

    /**
     * @param bool       $gearmanEnabled
     * @param array|null $gearmanServers
     */
    public function __construct($gearmanEnabled, array $gearmanServers = null) {
        $this->_gearmanEnabled = (bool) $gearmanEnabled;
        $this->_gearmanServers = (array) $gearmanServers;
    }

    /**
     * @return GearmanClient
     * @throws CM_Exception
     */
    public function getGearmanClient() {
        if (!extension_loaded('gearman')) {
            throw new CM_Exception('Missing `gearman` extension');
        }
        $gearmanClient = new GearmanClient();
        foreach ($this->_gearmanServers as $server) {
            $gearmanClient->addServer($server['host'], $server['port']);
        }
        return $gearmanClient;
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return (bool) $this->_gearmanEnabled;
    }
}
