<?php

namespace CM\Url;

use Psr\Http\Message\UriInterface;

interface UrlInterface extends UriInterface {

    /**
     * @return bool
     */
    public function isRelativeUrl();

    /**
     * @return bool
     */
    public function hasPathPrefix();

    /**
     * @return string
     */
    public function getPathPrefix();

    /**
     * @return UrlInterface
     */
    public function withPathPrefix($prefix);

    /**
     * @return UrlInterface
     */
    public function withoutPathPrefix();

    /**
     * @param UrlInterface $uri
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom(UrlInterface $uri);

    /**
     * @param \CM_Frontend_Environment $environment
     * @param array                    $options
     * @return UrlInterface
     */
    public function withEnvironment(\CM_Frontend_Environment $environment, array $options = null);

    /**
     * @param string $path
     * @return UrlInterface
     */
    public function withPath($path);

    /**
     * @param string $query
     * @return UrlInterface
     */
    public function withQuery($query);

    /**
     * @param string $fragment
     * @return UrlInterface
     */
    public function withFragment($fragment);
}
