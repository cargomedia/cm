<?php

namespace CM\Url;

use CM\Url\Components\PrefixedPath;
use CM\Url\Modifiers\Sanitize;
use League\Uri\Modifiers\Normalize;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Schemes\Http;

abstract class AbstractUrl extends Http implements UrlInterface {

    public function withPathPrefix($prefix) {
        return $this->withProperty('path', $this->_getPrefixedPathInstance()->withPrefix($prefix));
    }

    public function withoutPathPrefix() {
        return $this->withProperty('path', $this->_getPrefixedPathInstance()->withoutPrefix());
    }

    public function getPathPrefix() {
        return (string) ($this->path instanceof PrefixedPath ? $this->path->getPrefix() : null);
    }

    public function hasPathPrefix() {
        return $this->path instanceof PrefixedPath && $this->path->hasPrefix();
    }

    public function withRelativeComponentsFrom(UrlInterface $uri) {
        return $this
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }

    public function isRelativeUrl() {
        return '' === $this->getScheme() && '' === $this->getHost();
    }

    protected function withProperty($property, $value) {
        if ('path' === $property) {
            if (!$value instanceof PrefixedPath) {
                $value = new PrefixedPath($value, $this->getPathPrefix());
            }
            $url = clone $this;
            $url->$property = $value;
            $url->assertValidObject();
            return $url;
        } else {
            return parent::withProperty($property, $value);
        }
    }

    /**
     * @return PrefixedPath
     */
    protected function _getPrefixedPathInstance() {
        return $this->path instanceof PrefixedPath ? clone $this->path : new PrefixedPath($this->path);
    }

    /**
     * @return Pipeline
     */
    protected static function _getPipeline() {
        return new Pipeline([
            new Normalize(),
            new Sanitize(),
        ]);
    }

    /**
     * @param string|null $uri
     * @param string|null $pathPrefix
     * @return UrlInterface
     */
    public static function createFromString($uri = null, $pathPrefix = null) {
        /** @var AbstractUrl $url */
        $url = self::_getPipeline()->process(
            parent::createFromString((string) $uri)
        );
        if (null !== $pathPrefix) {
            $url = $url->withPathPrefix($pathPrefix);
        }
        return $url;
    }

    /**
     * @param array $components
     * @return UrlInterface
     */
    public static function createFromComponents(array $components) {
        $pathPrefix = null;
        if (isset($components['pathPrefix'])) {
            $pathPrefix = $components['pathPrefix'];
            unset($components['pathPrefix']);
        }
        /** @var AbstractUrl $url */
        $url = self::_getPipeline()->process(
            parent::createFromComponents($components)
        );
        return $url->withPathPrefix($pathPrefix);
    }
}
