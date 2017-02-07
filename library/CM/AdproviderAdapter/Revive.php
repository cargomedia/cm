<?php

class CM_AdproviderAdapter_Revive extends CM_AdproviderAdapter_Abstract {

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
        $html = '<div class="revive-ad" data-zone-id="' . CM_Util::htmlspecialchars($zoneId) . '" data-host="' . CM_Util::htmlspecialchars($host) .
            '" data-variables="' . CM_Util::htmlspecialchars(json_encode($variables, JSON_FORCE_OBJECT)) . '"></div>';
        return $html;
    }
}
