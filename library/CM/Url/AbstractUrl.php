<?php

namespace CM\Url;

use CM_Site_Abstract;
use CM_Frontend_Environment;
use CM_Model_Language;
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

    public function withSite(CM_Site_Abstract $site, $sameOrigin = null) {
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

    public function withEnvironment(CM_Frontend_Environment $environment, array $options = null) {
        $url = clone $this;
        if ($language = $environment->getLanguage()) {
            $url = $url->withLanguage($language);
        }
        return $url->withSite($environment->getSite());
    }

    protected function _ensureAbsolutePath() {
        return $this->withProperty('path', (string) $this->path->withLeadingSlash());
    }

    /**
     * @return string
     */
    abstract public function getUriRelativeComponents();

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
     * @param array|null                   $environmentOptions
     * @return AbstractUrl
     */
    protected static function _create($url, CM_Frontend_Environment $environment = null, array $environmentOptions = null) {
        /** @var AbstractUrl $abstractUrl */
        $abstractUrl = self::createFromString($url)->_ensureAbsolutePath();
        if ($environment) {
            $abstractUrl = $abstractUrl->withEnvironment($environment, $environmentOptions);
        }
        return $abstractUrl;
    }
}
