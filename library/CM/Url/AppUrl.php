<?php

namespace CM\Url;

use CM_Util;
use CM_Site_Abstract;
use CM_Site_SiteFactory;
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
     * @return static
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
     * @return static
     */
    public function withLanguage(CM_Model_Language $language) {
        $url = clone $this;
        $url->_language = $language;
        return $url;
    }

    /**
     * @return static
     */
    public function parseParameters() {
        /** @var AppUrl $url */
        $url = $this->parseLanguage();
        $url = $url->parseSite();
        $url = $url->parseDeployVersion();
        return $url;
    }

    /**
     * @return static
     */
    public function parseLanguage() {
        $language = null;
        $segments = $this->getSegments();
        foreach ($segments as $index => $segment) {
            if (preg_match('/language-([a-z]+)/', $segment, $matches)) {
                $language = CM_Model_Language::findByAbbreviation($matches[1]);
                if ($language) {
                    $this->_dropPathSegment($segment);
                }
            }
        }
        return $language ? $this->withLanguage($language) : $this;
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
     * @return static
     */
    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrl());
    }

    /**
     * @return static
     */
    public function parseSite() {
        $siteFactory = new CM_Site_SiteFactory();
        $site = $siteFactory->findSiteByUrl($this);
        if (!$site) {
            $segments = $this->getSegments();
            foreach ($segments as $index => $segment) {
                if (preg_match('/site-([0-9]+)/', $segment, $matches)) {
                    $site = $siteFactory->getSiteById($matches[1]);
                    if ($site) {
                        $this->_dropPathSegment($segment);
                    }
                }
            }
        }
        return $site ? $this->withSite($site) : $this;
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
     * @return static
     */
    public function parseDeployVersion() {
        $version = null;
        $segments = $this->getSegments();
        foreach ($segments as $index => $segment) {
            if (preg_match('/version-([0-9]+)/', $segment, $matches)) {
                $version = $matches[1];
                if ($version) {
                    $this->_dropPathSegment($segment);
                }
            }
        }
        $this->setDeployVersion($version);
        return $this;
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
            $this->getPathSegments()
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
     * @param string $uri
     * @return static
     */
    public static function createFromString($uri) {
        $url = new static(CM_Util::sanitizeUtf((string) $uri));
        return $url->parseParameters();
    }

    /**
     * @param string $uri
     * @return bool
     */
    public static function matchUri($uri) {
        return !!preg_match('/\/site-[0-9]+\//', $uri);
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
