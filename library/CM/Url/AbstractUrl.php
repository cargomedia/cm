<?php

namespace CM\Url;

use League\Uri\Interfaces\Uri;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

abstract class AbstractUrl extends AbstractHierarchicalUri implements UrlInterface {

    public function getRebaseUrl(AbsoluteUrl $baseUrl) {
        $rebasedPath = $this->path->prepend($baseUrl->path);
        $rebasedUrl = $this->withPath((string) $rebasedPath);
        return $baseUrl->withRelativeComponentsFrom($rebasedUrl);
    }

    /**
     * @param Uri $uri
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom(Uri $uri) {
        return $this
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
    }
}
