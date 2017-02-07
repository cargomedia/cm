<?php

namespace CM\Url;

use CM_Exception_Invalid;
use League\Uri\Components\HierarchicalPath;

class BaseUrl extends AbstractUrl {

    public function getUriRelativeComponents() {
        $path = HierarchicalPath::createFromSegments([], HierarchicalPath::IS_ABSOLUTE);
        if ($prefix = $this->getPrefix()) {
            $path = $path->append($prefix);
        }
        return $path->getUriComponent();
    }

    /**
     * @param string $url
     * @return BaseUrl
     * @throws CM_Exception_Invalid
     */
    public static function create($url) {
        /** @var BaseUrl $baseUrl */
        $baseUrl = parent::_create($url);
        if (!$baseUrl->isAbsolute()) {
            throw new CM_Exception_Invalid('BaseUrl::create argument must be an absolute Url', null, [
                'url' => $url,
            ]);
        }
        $path = $baseUrl->getPath();
        return $baseUrl
            ->withPrefix($path)
            ->withoutRelativeComponents();
    }
}
