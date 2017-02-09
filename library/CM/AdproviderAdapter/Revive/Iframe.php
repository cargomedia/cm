<?php

class CM_AdproviderAdapter_Revive_Iframe extends CM_AdproviderAdapter_Iframe {

    public function getHtml($zoneName, array $zoneData, array $variables = null) {
        if (!array_key_exists('zoneId', $zoneData)) {
            throw new CM_Exception_Invalid('Missing `zoneId`');
        }
        $zoneId = $zoneData['zoneId'];
        if (!array_key_exists('host', $zoneData)) {
            throw new CM_Exception_Invalid('Revive `host` missing');
        }
        $host = $zoneData['host'];
        $variables = (array) $variables;
        $variables['zoneid'] = $zoneId;
        $variables['cb'] = mt_rand();
        $zoneData['src'] = CM_Util::link('//' . $host . '/delivery/afr.php', $variables);
        return parent::getHtml($zoneName, $zoneData);
    }
}
