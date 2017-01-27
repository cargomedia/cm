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

    public function withEnvironment(CM_Frontend_Environment $environment, array $options = null) {
        $options = array_merge([
            'sameOrigin' => false,
        ], (array) $options);
        $site = $environment->getSite();

        $url = clone $this;
        if ($language = $environment->getLanguage()) {
            $url = $url->withLanguage($language);
        }
        return $url->withSite($site, $options['sameOrigin']);
    }

    public function withSite(CM_Site_Abstract $site, $sameOrigin = null) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($sameOrigin ? $site->getUrl() : $site->getUrlCdn());
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param array|null                   $environmentOptions
     * @param string|null                  $deployVersion
     * @return AssetUrl
     */
    protected static function _create($url, CM_Frontend_Environment $environment = null, array $environmentOptions = null, $deployVersion = null) {
        /** @var AssetUrl $assetUrl */
        $assetUrl = parent::_create($url, $environment, $environmentOptions);
        $assetUrl->setDeployVersion($deployVersion);
        return $assetUrl;
    }
}
