<?php

namespace CM\Url;

use CM_Exception_Invalid;

class BaseUrl extends Url {

    public function __construct($uri = '') {
        parent::__construct($uri);
        if ($this->isRelative()) {
            throw new CM_Exception_Invalid('BaseUrl::create argument must be an absolute Url', null, [
                'url' => $uri,
            ]);
        }
    }

    public function getUriRelativeComponents() {
        return $this->_getPathFromSegments();
    }

    public function getSegments() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        return $segments;
    }

    /**
     * @param string $url
     * @return BaseUrl
     * @throws CM_Exception_Invalid
     */
    public static function create($url) {
        $baseUrl = new static($url);
        /** @var BaseUrl $baseUrl */
        $baseUrl = $baseUrl
            ->withPrefix($baseUrl->getPath())
            ->withoutRelativeComponents();
        return $baseUrl;
    }
}
