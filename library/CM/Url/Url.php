<?php

namespace CM\Url;

use CM_Frontend_Environment;

class Url extends AbstractUrl {

    public function getUriRelativeComponents() {
        $segments = $this->getPathSegments();
        if ($language = $this->getLanguage()) {
            $segments = array_merge([$language->getAbbreviation()], $segments);
        }
        if ($prefix = $this->getPrefix()) {
            $segments = array_merge([$prefix], $segments);
        }
        return '/' . implode('/', $segments) . $this->getQueryComponent() . $this->getFragmentComponent();
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @return AbstractUrl
     */
    public static function create($url, CM_Frontend_Environment $environment = null) {
        return parent::_create($url, $environment);
    }

    /**
     * @param string      $uri
     * @param array|null  $params
     * @param string|null $fragment
     * @return static
     */
    public static function createWithParams($uri, array $params = null, $fragment = null) {
        /** @var Url $url */
        $url = new static($uri);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        if (null !== $fragment) {
            $url = $url->withFragment($fragment);
        }
        return $url;
    }

    /**
     * @param array $parts
     * @return static
     */
    public static function createFromParts(array $parts) {
        $url = new static();
        $url->applyParts($parts);
        return $url;
    }
}
