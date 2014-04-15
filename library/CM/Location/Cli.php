<?php

class CM_Location_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $geoIpFile
     * @param string|null $verbose
     * @synchronized
     */
    public function update($geoIpFile = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getOutput(), $verbose);
        $maxMind->update();
    }

    /**
     * @param string|null $geoIpFile
     * @param string|null $verbose
     * @synchronized
     */
    public function upgrade($geoIpFile = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getOutput(), $verbose);
        $maxMind->upgrade();
    }

    public static function getPackageName() {
        return 'location';
    }
}
