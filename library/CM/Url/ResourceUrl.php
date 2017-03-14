<?php

namespace CM\Url;

use CM_Frontend_Environment;
use League\Uri\Components\HierarchicalPath;

class ResourceUrl extends AssetUrl {

    /** @var string */
    protected $_type;

    /**
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->_type = (string) $type;
    }

    public function getUriRelativeComponents() {
        $segments = [
            $this->getType(),
        ];
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        if ($site = $this->getSite()) {
            $segments[] = $site->getType();
        }
        if ($deployVersion = $this->getDeployVersion()) {
            $segments[] = $deployVersion;
        }
        $path = $this->path->prepend(
            HierarchicalPath::createFromSegments($segments, HierarchicalPath::IS_ABSOLUTE)
        );
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string                       $url
     * @param string                       $type
     * @param CM_Frontend_Environment|null $environment
     * @param array|null                   $environmentOptions
     * @param string|null                  $deployVersion
     * @return ResourceUrl
     */
    public static function create($url, $type, CM_Frontend_Environment $environment = null, array $environmentOptions = null, $deployVersion = null) {
        /** @var ResourceUrl $resourceUrl */
        $resourceUrl = parent::_create($url, $environment, $environmentOptions, $deployVersion);
        $resourceUrl->setType($type);
        return $resourceUrl;
    }
}
