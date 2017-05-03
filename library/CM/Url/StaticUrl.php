<?php

namespace CM\Url;

use CM_Site_Abstract;
use CM_Frontend_Environment;

class StaticUrl extends Url {

    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrlCdn());
    }

    public function getUriRelativeComponents() {
        $query = $this->_getQueryComponent();
        if ($deployVersion = $this->getDeployVersion()) {
            $query .= (!empty($query) ? '&' : '?') . $this->getDeployVersion();
        }
        return $this->_getPathFromSegments() . $query . $this->_getFragmentComponent();
    }

    protected function _getParameterSegments() {
        return ['static'];
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return StaticUrl
     */
    public static function create($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var StaticUrl $url */
        $url = parent::createWithEnvironment($url, $environment, $deployVersion);
        return $url;
    }
}
