<?php

namespace CM\Url;

use CM_Frontend_Environment;
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
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return StaticUrl
     */
    public static function create($url, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var StaticUrl $staticUrl */
        $staticUrl = parent::_create($url, $environment, $deployVersion);
        return $staticUrl;
    }
}
