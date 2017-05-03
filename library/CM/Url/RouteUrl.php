<?php

namespace CM\Url;

use CM_Frontend_Environment;

class RouteUrl extends Url {

    /**
     * @param string                       $route
     * @param array|null                   $params
     * @param CM_Frontend_Environment|null $environment
     * @return RouteUrl
     */
    public static function create($route, array $params = null, CM_Frontend_Environment $environment = null) {
        /** @var RouteUrl $url */
        $url = parent::createWithEnvironment($route, $environment);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        return $url;
    }
}
