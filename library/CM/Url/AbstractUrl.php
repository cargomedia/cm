<?php

namespace CM\Url;

use Psr\Http\Message\UriInterface;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

abstract class AbstractUrl extends AbstractHierarchicalUri implements UrlInterface {

    public function getRebaseUrl(UrlInterface $baseUrl) {
        $baseUrl = AbsoluteUrl::createFromString((string) $baseUrl);
        $rebasedPath = $this->path->prepend($baseUrl->path);
        $rebasedUrl = $this->withPath((string) $rebasedPath);
        return $baseUrl->withRelativeComponentsFrom($rebasedUrl);
    }

    /**
     * @param UriInterface $uri
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom(UriInterface $uri) {
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
}
