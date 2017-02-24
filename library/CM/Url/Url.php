<?php

namespace CM\Url;

use CM_Frontend_Environment;

class Url extends AbstractUrl {

    public function getUriRelativeComponents() {
        $path = clone $this->path;
        if ($language = $this->getLanguage()) {
            $path = $path->prepend($language->getAbbreviation());
        }
        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
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
        $url = self::createFromString($uri);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        if (null !== $fragment) {
            $url = $url->withFragment($fragment);
        }
        return $url;
    }
}
