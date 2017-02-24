<?php

namespace CM\Url;

use CM_Exception_Invalid;

class BaseUrl extends AbstractUrl {

    public function __construct($uri = '') {
        parent::__construct($uri);
        if (!$this->isAbsolute()) {
            throw new CM_Exception_Invalid('BaseUrl::create argument must be an absolute Url', null, [
                'url' => $uri,
            ]);
        }
    }

    public function getUriRelativeComponents() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments = array_merge([$prefix], $segments);
        }
        return '/' . implode('/', $segments);
    }

    /**
     * @param string $url
     * @return BaseUrl
     * @throws CM_Exception_Invalid
     */
    public static function create($url) {
        $baseUrl = parent::_create($url);
        /** @var BaseUrl $baseUrl */
        $baseUrl = $baseUrl
            ->withPrefix($baseUrl->getPath())
            ->withoutRelativeComponents();
        return $baseUrl;
    }
}
