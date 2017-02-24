<?php

namespace CM\Url;

use CM_Frontend_Environment;

class StaticUrl extends AssetUrl {

    public function getUriRelativeComponents() {
        $query = $this->getQueryComponent();
        if ($deployVersion = $this->getDeployVersion()) {
            $query .= (!empty($query) ? '&' : '?') . $this->getDeployVersion();
        }
        $segments = array_merge(['static'], $this->getPathSegments());
        return '/' . implode('/', $segments) . $query . $this->getFragmentComponent();
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return StaticUrl
     */
    public static function create($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var StaticUrl $staticUrl */
        $staticUrl = parent::_create($url, $environment, $deployVersion);
        return $staticUrl;
    }
}
