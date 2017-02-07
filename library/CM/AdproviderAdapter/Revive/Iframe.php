<?php

class CM_AdproviderAdapter_Revive_Iframe extends CM_AdproviderAdapter_Iframe {

    public function getHtml($zoneName, $zoneData, array $variables) {
        if (!array_key_exists('zoneId', $zoneData)) {
            throw new CM_Exception_Invalid('Missing `zoneId`');
        }
        $zoneId = $zoneData['zoneId'];
        if (!array_key_exists('host', $zoneData)) {
            throw new CM_Exception_Invalid('Revive `host` missing');
        }
        $host = $zoneData['host'];
        $zoneData['src'] = '//' . $host . '/delivery/afr.php';
        $query = array_merge($variables, [
            'zoneId' => $zoneId,
            'cb'     => mt_rand(),
        ]);
        $zoneData['src'] .= '?' . CM_Util::http_build_query($query);
        return parent::getHtml($zoneName, $zoneData, []);
    }
}
