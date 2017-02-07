<?php

namespace CM\Url;

use CM_Params;
use CM_Site_Abstract;
use CM_Frontend_Environment;
use CM_Model_Language;
use League\Uri\Components\Query;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Modifiers\Normalize;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Schemes\Http;
use Psr\Http\Message\UriInterface;

abstract class AbstractUrl extends Http implements UrlInterface {

    protected static $supportedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    /** @var array|null */
    protected $_params = null;

    /** @var HierarchicalPath|null */
    protected $_prefix = null;

    /** @var CM_Model_Language|null */
    protected $_language = null;

    /** @var CM_Site_Abstract|null */
    protected $_site = null;

    public function isAbsolute() {
        return !('' === $this->getScheme() && '' === $this->getHost());
    }

    public function getLanguage() {
        return $this->_language;
    }

    public function getSite() {
        return $this->_site;
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
            $prefix = new HierarchicalPath((string) $prefix);
            $prefix = $prefix
                ->withoutLeadingSlash()
                ->withoutTrailingSlash()
                ->withoutDotSegments()
                ->withoutEmptySegments();
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
        /** @var AbstractUrl $url */
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
            $url = Http::createFromString($url);
        }
        return $this
            ->withPath($url->getPath())
            ->withQuery($url->getQuery())
            ->withFragment($url->getFragment());
    }

    public function withoutRelativeComponents() {
        return $this->withRelativeComponentsFrom('/');
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

    /**
     * @return string
     */
    abstract public function getUriRelativeComponents();

    protected function _ensureAbsolutePath() {
        return $this->withProperty('path', (string) $this->path->withLeadingSlash());
    }

    protected function getSchemeSpecificPart() {
        $authority = $this->getAuthority();

        $res = array_filter([
            $this->userInfo->getContent(),
            $this->host->getContent(),
            $this->port->getContent(),
        ], function ($value) {
            return null !== $value;
        });

        if (!empty($res)) {
            $authority = '//' . $authority;
        }

        return $authority . $this->getUriRelativeComponents();
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @return AbstractUrl
     */
    protected static function _create($url, CM_Frontend_Environment $environment = null) {
        /** @var AbstractUrl $abstractUrl */
        $abstractUrl = self::getPipeline()->process(
            self::createFromString($url)
        );
        $abstractUrl = $abstractUrl->_ensureAbsolutePath();
        if ($environment) {
            $abstractUrl = $abstractUrl->withEnvironment($environment);
        }
        return $abstractUrl;
    }

    /**
     * @return Pipeline
     */
    public static function getPipeline() {
        return new Pipeline([
            new Normalize(),
        ]);
    }
}
