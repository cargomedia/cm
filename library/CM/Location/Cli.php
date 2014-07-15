<?php

class CM_Location_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param CM_File|null $geoIpFile
     * @param bool|null    $verbose
     * @synchronized
     */
    public function outdated(CM_File $geoIpFile = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getStreamOutput(), $this->_getStreamError(), null, $verbose);
        $maxMind->outdated();
    }

    /**
     * @param CM_File|null $geoIpFile
     * @param bool|null    $withoutIpBlocks
     * @param bool|null    $verbose
     * @synchronized
     */
    public function upgrade(CM_File $geoIpFile = null, $withoutIpBlocks = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getStreamOutput(), $this->_getStreamError(), $withoutIpBlocks, $verbose);
        $maxMind->upgrade();
    }

    public static function getPackageName() {
        return 'location';
    }
}
