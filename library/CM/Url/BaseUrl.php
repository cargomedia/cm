<?php

namespace CM\Url;

use CM_Exception_Invalid;
use CM_Frontend_Environment;
use CM_Model_Language;
use CM_Site_Abstract;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Schemes\Http;

class BaseUrl extends AbstractUrl {

    protected function _getUriRelativeComponents() {
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
