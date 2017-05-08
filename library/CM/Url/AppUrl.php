<?php

namespace CM\Url;

use CM_Site_Abstract;
use CM_Frontend_Environment;
use CM_Model_Language;

class AppUrl extends Url {

    /** @var CM_Model_Language|null */
    protected $_language = null;

    /** @var CM_Site_Abstract|null */
    protected $_site = null;

    /** @var string|null */
    protected $_deployVersion = null;

    /**
     * @param CM_Frontend_Environment $environment
     * @return AppUrl
     */
    public function withEnvironment(CM_Frontend_Environment $environment) {
        $url = clone $this;
        if ($language = $environment->getLanguage()) {
            $url = $url->withLanguage($language);
        }
        return $url->withSite($environment->getSite());
    }

    /**
     * @param CM_Model_Language $language
     * @return AppUrl
     */
    public function withLanguage(CM_Model_Language $language) {
        $url = clone $this;
        $url->_language = $language;
        return $url;
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage() {
        return $this->_language;
    }

    /**
     * @return string|null
     */
    public function getLanguageSegment() {
        $language = $this->getLanguage();
        return $language ? 'language-' . $language->getAbbreviation() : null;
    }

    /**
     * @param CM_Site_Abstract $site
     * @return AppUrl
     */
    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrl());
    }

    /**
     * @return CM_Site_Abstract|null
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @return string|null
     */
    public function getSiteSegment() {
        $site = $this->getSite();
        return $site ? 'site-' . $site->getId() : null;
    }

    /**
     * @param string|null $deployVersion
     */
    public function setDeployVersion($deployVersion) {
        $this->_deployVersion = $deployVersion;
    }

    /**
     * @return string|null
     */
    public function getDeployVersion() {
        return $this->_deployVersion;
    }

    /**
     * @return string|null
     */
    public function getDeployVersionSegment() {
        $version = $this->getDeployVersion();
        return $version ? 'version-' . $version : null;
    }

    /**
     * @return array
     */
    public function getSegments() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        return array_merge(
            $segments,
            $this->_getParameterSegments(),
            explode('/', $this->path)
        );
    }

    /**
     * @return array
     */
    protected function _getParameterSegments() {
        $segments = [];
        if ($languageSegment = $this->getLanguageSegment()) {
            $segments[] = $languageSegment;
        }
        if ($siteSegment = $this->getSiteSegment()) {
            $segments[] = $siteSegment;
        }
        if ($versionSegment = $this->getDeployVersionSegment()) {
            $segments[] = $versionSegment;
        }
        return $segments;
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return static
     */
    public static function createWithEnvironment($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        $url = new static($url);
        $url->setDeployVersion($deployVersion);
        if ($environment) {
            $url = $url->withEnvironment($environment);
        }
        return $url;
    }
}
