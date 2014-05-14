<?php

class CM_Location_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param CM_File|null $geoIpFile
     * @param bool|null $verbose
     * @synchronized
     */
    public function update(CM_File $geoIpFile = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getOutput(), null, $verbose);
        $maxMind->update();
    }

    /**
     * @param CM_File|null $geoIpFile
     * @param bool|null $withoutIpBlocks
     * @param bool|null $verbose
     * @synchronized
     */
    public function upgrade(CM_File $geoIpFile = null, $withoutIpBlocks = null, $verbose = null) {
        $maxMind = new CMService_MaxMind($geoIpFile, $this->_getOutput(), $withoutIpBlocks, $verbose);
        $maxMind->upgrade();
    }

    public static function getPackageName() {
        return 'location';
    }
}
