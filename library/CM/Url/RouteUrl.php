<?php

namespace CM\Url;

use CM_Frontend_Environment;

class RouteUrl extends AbstractUrl {

    public function getUriRelativeComponents() {
        $segments = $this->getPathSegments();
        if ($prefix = $this->getPrefix()) {
            $segments = array_merge([$prefix], $segments);
        }
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        return '/' . implode('/', $segments) . $this->getQueryComponent() . $this->getFragmentComponent();
    }

    /**
     * @param string                       $route
     * @param array|null                   $params
     * @param CM_Frontend_Environment|null $environment
     * @return RouteUrl
     */
    public static function create($route, array $params = null, CM_Frontend_Environment $environment = null) {
        /** @var RouteUrl $url */
        $url = parent::_create($route, $environment);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        return $url;
    }
}
