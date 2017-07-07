<?php

class CM_Service_NetworkTools {

    /**
     * @param string $domain
     * @return bool
     */
    public function hasMXRecords($domain) {
        return getmxrr($domain, $hostList);
    }
}
