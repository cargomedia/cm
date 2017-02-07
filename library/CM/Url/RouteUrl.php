<?php

namespace CM\Url;

use CM_Frontend_Environment;
use League\Uri\Components\Query;

class RouteUrl extends AbstractUrl {

    public function getUriRelativeComponents() {
        $path = $this->path;
        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }
        if ($language = $this->getLanguage()) {
            $path = $path->append($language->getAbbreviation());
        }
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
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
