<?php

class CM_Adprovider_Adapter_Openx extends CM_Adprovider_Adapter_Abstract {

    /** @var string */
    private $_host;

    /**
     * @param string $host
     */
    public function __construct($host) {
        $this->_host = (string) $host;
    }

    public function getHtml($zoneData, array $variables) {
        if (!array_key_exists('zoneId', $zoneData)) {
            throw new CM_Exception_Invalid('Missing `zoneId`');
        }
        $zoneId = $zoneData['zoneId'];
        $host = $this->_host;
        $html = '<div class="openx-ad" data-zone-id="' . CM_Util::htmlspecialchars($zoneId) . '" data-host="' . CM_Util::htmlspecialchars($host) .
            '" data-variables="' . CM_Util::htmlspecialchars(json_encode($variables, JSON_FORCE_OBJECT)) . '"></div>';
        return $html;
    }
}
