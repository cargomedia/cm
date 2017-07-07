<?php

class CM_Service_NetworkTools {

    /**
     * @param string $domain
     * @return bool
     */
    public function getMXRecords($domain) {
        return getmxrr($domain, $hostList);
    }
}
