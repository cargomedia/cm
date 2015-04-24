<?php

class CM_AdproviderAdapter_Epom extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneName, $zoneData, array $variables) {
        $zoneId = CM_Util::htmlspecialchars($zoneData['zoneId']);
        $variables = $this->_variableKeysToUnderscore($variables);
        $variables['key'] = $zoneData['accessKey'];

        $html = '<div id="epom-' . $zoneId . '" class="epom-ad" data-zone-id="' . $zoneId . '" data-variables="' .
            CM_Util::htmlspecialchars(json_encode($variables, JSON_FORCE_OBJECT)) . '"></div>';
        return $html;
    }

    private function _variableKeysToUnderscore($variables) {
        foreach ($variables as $key => $value) {
            unset ($variables[$key]);
            $underscoreKey = str_replace('-', '_', $key);
            $variables[$underscoreKey] = $value;
        }
        return $variables;
    }
}
