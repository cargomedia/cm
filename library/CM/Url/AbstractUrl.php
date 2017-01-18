<?php

namespace CM\Url;

use CM\Url\Modifiers\Sanitize;
use League\Uri\Modifiers\Normalize;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Schemes\Http;

abstract class AbstractUrl extends Http implements UrlInterface {

    public function getRebaseUrl(UrlInterface $baseUrl) {
        $baseUrl = AbsoluteUrl::createFromString((string) $baseUrl);
        $rebasedPath = $this->path->prepend($baseUrl->path);
        $rebasedUrl = $this->withPath((string) $rebasedPath);
        return $baseUrl->withRelativeComponentsFrom($rebasedUrl);
    }

    /**
     * @param UrlInterface $uri
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom(UrlInterface $uri) {
        return $this
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }

    /**
     * @return bool
     */
    public function isRelativeUrl() {
        return '' === $this->getScheme() && '' === $this->getHost();
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

    public static function createFromString($uri = '') {
        return self::_getPipeline()->process(
            parent::createFromString($uri)
        );
    }

    public static function createFromComponents(array $components) {
        return self::_getPipeline()->process(
            parent::createFromComponents($components)
        );
    }
}
