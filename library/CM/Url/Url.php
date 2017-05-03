<?php

namespace CM\Url;

use CM_Params;
use CM_Site_Abstract;
use CM_Frontend_Environment;
use CM_Model_Language;

use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\UriResolver;
use CM\Url\Vendor\Uri;

class Url extends Uri implements UrlInterface {

    /** @var array|null */
    protected $_params = null;

    /** @var string|null */
    protected $_prefix = null;

    /** @var CM_Model_Language|null */
    protected $_language = null;

    /** @var CM_Site_Abstract|null */
    protected $_site = null;

    /** @var string|null */
    protected $_deployVersion = null;

    public function isRelative() {
        return '' === $this->getScheme() && '' === $this->getHost();
    }

    public function getLanguage() {
        return $this->_language;
    }

    /**
     * @return null|string
     */
    public function getLanguageSegment() {
        $language = $this->getLanguage();
        return $language ? 'language-' . $language->getAbbreviation() : null;
    }

    public function getSite() {
        return $this->_site;
    }

    /**
     * @return null|string
     */
    public function getSiteSegment() {
        $site = $this->getSite();
        return $site ? 'site-' . $site->getId() : null;
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
     * @param string|null $deployVersion
     */
    public function setDeployVersion($deployVersion) {
        $this->_deployVersion = $deployVersion;
    }

    public function getPrefix() {
        if (null === $this->_prefix) {
            return null;
        }
        return (string) $this->_prefix;
    }

    public function getParams() {
        return $this->_params;
    }

    public function hasTrailingSlash() {
        return '/' === substr($this->getPath(), -1);
    }

    public function withTrailingSlash() {
        $new = clone $this;
        if (!$new->hasTrailingSlash()) {
            $new->path .= '/';
        }
        return $new;
    }

    public function withoutTrailingSlash() {
        $new = clone $this;
        if ($new->hasTrailingSlash()) {
            $new->path = rtrim($new->path, '/');
        }
        return $new;
    }

    public function withPath($path) {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }
        $new = clone $this;
        $new->path = UriResolver::removeDotSegments($path);
        return $new;
    }

    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrl());
    }

    public function withLanguage(CM_Model_Language $language) {
        $url = clone $this;
        $url->_language = $language;
        return $url;
    }

    public function withPrefix($prefix) {
        if (null !== $prefix) {
            $prefix = trim($this->filterPath(UriResolver::removeDotSegments($prefix)), '/');
        }
        $prefix = '' !== (string) $prefix ? $prefix : null;
        $url = clone $this;
        $url->_prefix = $prefix;
        return $url;
    }

    public function withoutPrefix() {
        $url = clone $this;
        $url->_prefix = null;
        return $url;
    }

    public function withParams(array $params) {
        $this->_params = $params;
        $params = CM_Params::encode($this->getParams());
        $query = http_build_query($params);
        return parent::withQuery($query);
    }

    public function withQuery($queryString) {
        $params = [];
        parse_str($queryString, $params);
        $this->_params = $params;
        return parent::withQuery($queryString);
    }

    public function withBaseUrl($baseUrl) {
        if (!$baseUrl instanceof BaseUrl) {
            $baseUrl = BaseUrl::create((string) $baseUrl);
        }
        /** @var Url $url */
        $url = $this
            ->withHost($baseUrl->getHost())
            ->withScheme($baseUrl->getScheme());

        if ($prefix = $baseUrl->getPrefix()) {
            $url = $url->withPrefix($prefix);
        }
        return $url;
    }

    public function withRelativeComponentsFrom($url) {
        if (!$url instanceof UriInterface) {
            $url = new Uri($url);
        }
        return $this
            ->withPath($url->getPath())
            ->withQuery($url->getQuery())
            ->withFragment($url->getFragment());
    }

    public function withoutRelativeComponents() {
        $url = $this;
        $url->path = null;
        $url->query = null;
        $url->fragment = null;
        return $url;
    }

    public function withEnvironment(CM_Frontend_Environment $environment) {
        $url = clone $this;
        if ($language = $environment->getLanguage()) {
            $url = $url->withLanguage($language);
        }
        return $url->withSite($environment->getSite());
    }

    public function getUriBaseComponents() {
        $baseUrl = sprintf('%s://%s', $this->getScheme(), $this->getAuthority());
        if ($prefix = $this->getPrefix()) {
            $baseUrl = sprintf('%s/%s', $baseUrl, $prefix);
        }
        return $baseUrl;
    }

    public function getUriRelativeComponents() {
        return $this->_getPathFromSegments() . $this->_getQueryComponent() . $this->_getFragmentComponent();
    }

    public function __toString() {
        return $this->getSchemeSpecificPart();
    }

    protected function applyParts(array $parts) {
        parent::applyParts($parts);
        $this->_ensureAbsolutePath();
        $this->path = UriResolver::removeDotSegments($this->path);
    }

    /**
     * @return array
     */
    protected function _getSegments() {
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
     * @return string
     */
    protected function _getQueryComponent() {
        $query = (string) $this->query;
        return !empty($query) ? '?' . $query : $query;
    }

    /**
     * @return string
     */
    protected function _getFragmentComponent() {
        $fragment = (string) $this->fragment;
        return !empty($fragment) ? '#' . $fragment : $fragment;
    }

    /**
     * @return string
     */
    protected function _getPathFromSegments() {
        $segments = $this->_getSegments();
        if (0 === count($segments) && '' === $this->path) {
            return '';
        }
        $segments = $this->_filterPathSegments($segments);
        $path = '/' . implode('/', $segments);
        if ($this->hasTrailingSlash() && '/' !== $path) {
            $path .= '/';
        }
        return $path;
    }

    /**
     * @param array|null $segments
     * @return array
     */
    protected function _filterPathSegments(array $segments = null) {
        return array_filter((array) $segments, function ($value) {
            return null !== $value && '' !== $value;
        });
    }

    protected function _ensureAbsolutePath() {
        $path = $this->getPath();
        if ('' === $path || '/' !== mb_substr($path, 0, 1, 'UTF-8')) {
            $this->path = '/' . $path;
        }
    }

    /**
     * @return string
     */
    protected function getSchemeSpecificPart() {
        $scheme = $this->scheme;
        $authority = $this->getAuthority();
        if (!empty($authority)) {
            $authority = (!empty($scheme) ? $scheme . ':' : '') . '//' . $authority;
        }
        return $authority . $this->getUriRelativeComponents();
    }

    /**
     * @param string      $uri
     * @param array|null  $params
     * @param string|null $fragment
     * @return static
     */
    public static function createWithParams($uri, array $params = null, $fragment = null) {
        /** @var Url $url */
        $url = new static($uri);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        if (null !== $fragment) {
            $url = $url->withFragment($fragment);
        }
        return $url;
    }

    /**
     * @param array $parts
     * @return static
     */
    public static function createFromParts(array $parts) {
        $url = new static();
        $url->applyParts($parts);
        return $url;
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return Url
     */
    public static function createWithEnvironment($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var Url $url */
        $url = new static($url);
        $url->setDeployVersion($deployVersion);
        if ($environment) {
            $url = $url->withEnvironment($environment);
        }
        return $url;
    }
}
