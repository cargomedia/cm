<?php

namespace CM\Url;

use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Query;

class StaticUrl extends AssetUrl {

    public function getUriRelativeComponents() {
        $path = $this->path->prepend(
            HierarchicalPath::createFromSegments(['static'], HierarchicalPath::IS_ABSOLUTE)
        );
        /** @var Query $query */
        $query = $this->query;
        if ($deployVersion = $this->getDeployVersion()) {
            $pairs = $query->getPairs();
            $pairs[(string) $deployVersion] = null;
            $query = Query::createFromPairs($pairs);
        }
        return ''
            . $path->getUriComponent()
            . $query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string            $url
     * @param UrlInterface|null $baseUrl
     * @param string|null       $deployVersion
     * @return StaticUrl
     */
    public static function create($url, UrlInterface $baseUrl = null, $deployVersion = null) {
        /** @var StaticUrl $staticUrl */
        $staticUrl = parent::_create($url, $baseUrl, null, $deployVersion);
        return $staticUrl;
    }
}
