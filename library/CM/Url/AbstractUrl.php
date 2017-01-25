<?php

namespace CM\Url;

use CM_Exception_Invalid;
use CM_Frontend_Environment;
use CM_Model_Language;
use League\Uri\Modifiers\Normalize;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Schemes\Http;

abstract class AbstractUrl extends Http implements UrlInterface {

    protected static $supportedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    /** @var CM_Model_Language|null */
    protected $_language = null;

    /** @var string|null */
    protected $_prefix = null;

    public function isAbsolute() {
        return !('' === $this->getScheme() && '' === $this->getHost());
    }

    public function getLanguage() {
        return $this->_language;
    }

    public function getPrefix() {
        return $this->_prefix;
    }

    public function withLanguage(CM_Model_Language $language) {
        $url = clone $this;
        $url->_language = $language;
        return $url;
    }

    public function withPrefix($prefix) {
        if (!$prefix) {
            $prefix = null;
        }
        $url = clone $this;
        $url->_prefix = $prefix;
        return $url;
    }

    public function withBaseUrl(UrlInterface $baseUrl) {
        if (!$baseUrl->isAbsolute()) {
            throw new CM_Exception_Invalid('baseUrl must be absolute', null, [
                'baseUrl'   => (string) $baseUrl,
                'targetUrl' => (string) $this,
            ]);
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

    public function withRelativeComponentsFrom(UrlInterface $url) {
        return $this
            ->withPath($url->getPath())
            ->withQuery($url->getQuery())
            ->withFragment($url->getFragment());
    }

    public function withEnvironment(CM_Frontend_Environment $environment, array $options = null) {
        $url = clone $this;

        if ($language = $environment->getLanguage()) {
            $url = $url->withLanguage($language);
        }
        $baseUrl = $environment->getSite()->getUrlBase();
        if (!$baseUrl instanceof UrlInterface) {
            $baseUrl = $this->_create((string) $baseUrl);
        }
        return $url->withBaseUrl($baseUrl);
    }

    protected function _ensureAbsolutePath() {
        return $this->withProperty('path', (string) $this->path->withLeadingSlash());
    }

    /**
     * @return string
     */
    abstract protected function _getUriRelativeComponents();

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

        return $authority . $this->_getUriRelativeComponents();
    }

    /**
     * @param string                 $url
     * @param UrlInterface|null      $baseUrl
     * @param CM_Model_Language|null $language
     * @return static
     */
    protected static function _create($url, UrlInterface $baseUrl = null, CM_Model_Language $language = null) {
        /** @var AbstractUrl $url */
        $url = self::getPipeline()->process(
            parent::createFromString($url)
        );
        $url = $url->_ensureAbsolutePath();
        if ($baseUrl) {
            $url = $url->withBaseUrl($baseUrl);
        }
        if ($language) {
            $url = $url->withLanguage($language);
        }
        return $url;
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
