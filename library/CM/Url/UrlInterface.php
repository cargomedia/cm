<?php

namespace CM\Url;

use League\Uri\Interfaces\Uri;

interface UrlInterface extends Uri {

    /**
     * @param AbsoluteUrl $url
     * @return AbsoluteUrl
     */
    public function getRebaseUrl(AbsoluteUrl $url);

    /**
     * @param \CM_Frontend_Environment $environment
     * @param array                    $options
     * @return AbsoluteUrl
     */
    public function withEnvironment(\CM_Frontend_Environment $environment, array $options = null);
}
