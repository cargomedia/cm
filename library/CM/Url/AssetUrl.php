<?php

namespace CM\Url;

use CM_Frontend_Environment;
use CM_Site_Abstract;

abstract class AssetUrl extends AbstractUrl {

    /** @var string|null */
    protected $_deployVersion;

    /**
     * @return string|null
     */
    public function getDeployVersion() {
        return $this->_deployVersion;
    }

    /**
     * @param string|null $deployVersion
     */
    public function setDeployVersion($deployVersion) {
        $this->_deployVersion = $deployVersion;
    }

    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrlCdn());
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return AssetUrl
     */
    protected static function _create($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var AssetUrl $assetUrl */
        $assetUrl = parent::_create($url, $environment);
        $assetUrl->setDeployVersion($deployVersion);
        return $assetUrl;
    }
}
