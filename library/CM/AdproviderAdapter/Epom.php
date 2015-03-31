<?php

class CM_AdproviderAdapter_Epom extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneData, array $variables) {
        $zoneId = CM_Util::htmlspecialchars($zoneData['zoneId']);
        $variables = $this->_variableKeysToUnderscore($variables);
        $variables['key'] = '2ea5b261f06ca771033a5fa9e22493f1';

        $html = '<div id="epom-' . $zoneId . '-' . '2ea5b261f06ca771033a5fa9e22493f1-300x100' . '" class="epom-ad" data-zone-id="' . $zoneId . '" data-variables="' .
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
