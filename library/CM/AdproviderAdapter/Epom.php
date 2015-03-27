<?php

class CM_AdproviderAdapter_Epom extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneData, array $variables) {
        $zoneId = CM_Util::htmlspecialchars($zoneData['zoneId']);
        $variables = $this->_variablesToCamelCase($variables);
        $variables['key'] = '2ea5b261f06ca771033a5fa9e22493f1';

        $html = '<div id="epom-' . $zoneId . '-' . '2ea5b261f06ca771033a5fa9e22493f1-300x100' . '" class="epom-ad" data-zone-id="' . $zoneId . '" data-variables="' .
            CM_Util::htmlspecialchars(json_encode($variables, JSON_FORCE_OBJECT)) . '"></div>';
        return $html;
    }

    private function _variablesToCamelCase($variables) {
        foreach ($variables as $key => $value) {
            unset ($variables[$key]);
            $camelCaseKey = $this->_dashesToCamelCase($key);
            $variables[$camelCaseKey] = $value;
        }
        return $variables;
    }

    private function _dashesToCamelCase($string) {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
