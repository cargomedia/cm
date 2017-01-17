<?php

namespace CM\Url;

use Psr\Http\Message\UriInterface;

interface UrlInterface extends UriInterface {

    /**
     * @param UrlInterface $url
     * @return UrlInterface
     */
    public function getRebaseUrl(UrlInterface $url);

    /**
     * @param \CM_Frontend_Environment $environment
     * @param array                    $options
     * @return UrlInterface
     */
    public function withEnvironment(\CM_Frontend_Environment $environment, array $options = null);
}
