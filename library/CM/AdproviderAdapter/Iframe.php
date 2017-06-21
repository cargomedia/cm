<?php

class CM_AdproviderAdapter_Iframe extends CM_AdproviderAdapter_Abstract {

    public function getHtml($zoneName, array $zoneData, array $variables = null) {
        $src = (string) $zoneData['src'];
        $width = (string) $zoneData['width'];
        $height = (string) $zoneData['height'];
        $variables = (array) $variables;

        $params = [
            'src'            => $src,
            'width'          => $width,
            'height'         => $height,
            'class'          => 'Adv3rt153m3nt-hasContent',
            'frameborder'    => 0,
            'scrolling'      => 'no',
            'data-variables' => json_encode($variables, JSON_FORCE_OBJECT),
        ];
        $params = Functional\map($params, function ($value, $key) {
            return $key . '="' . CM_Util::htmlspecialchars($value) . '"';
        });

        return '<iframe ' . join(' ', $params) . '></iframe>';
    }
}
