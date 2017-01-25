<?php

namespace CM\Url;

use CM_Frontend_Environment;
use CM_Model_Language;
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

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $sameOrigin
     * @return AssetUrl
     */
    public function withSite(CM_Site_Abstract $site, $sameOrigin = null) {
        $sameOrigin = (bool) $sameOrigin;
        $baseUrl = $site->getUrlBase();
        if (!$sameOrigin && $cdnUrl = $site->getUrlCdn()) {
            $baseUrl = $cdnUrl;
        }
        if (!$baseUrl instanceof UrlInterface) {
            $baseUrl = $this->_create((string) $baseUrl);
        }
        return $this->withBaseUrl($baseUrl);
    }

    /**
     * @param string                 $filename
     * @param UrlInterface|null      $baseUrl
     * @param CM_Model_Language|null $language
     * @param string|null            $deployVersion
     * @return AssetUrl
     */
    protected static function _create($filename, UrlInterface $baseUrl = null, CM_Model_Language $language = null, $deployVersion = null) {
        /** @var AssetUrl $url */
        $url = parent::_create($filename, $baseUrl, $language);
        $url->setDeployVersion($deployVersion);
        return $url;
    }
}
