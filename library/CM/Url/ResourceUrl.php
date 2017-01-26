<?php

namespace CM\Url;

use CM_Model_Language;
use CM_Site_Abstract;
use League\Uri\Components\HierarchicalPath;

class ResourceUrl extends AssetUrl {

    /** @var CM_Site_Abstract|null */
    protected $_site;

    /** @var string */
    protected $_type;

    /**
     * @return CM_Site_Abstract|null
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @param CM_Site_Abstract|null $site
     */
    public function setSite(CM_Site_Abstract $site = null) {
        $this->_site = $site;
    }

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

    public function withSite(CM_Site_Abstract $site, $sameOrigin = null) {
        /** @var ResourceUrl $url */
        $url = parent::withSite($site, $sameOrigin);
        $url->setSite($site);
        return $url;
    }

    protected function _getUriRelativeComponents() {
        $segments = [
            $this->getType(),
        ];
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        if ($site = $this->getSite()) {
            $segments[] = $site->getId();
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
     * @param string                 $url
     * @param string                 $type
     * @param CM_Model_Language|null $language
     * @param string|null            $deployVersion
     * @return ResourceUrl
     */
    public static function create($url, $type, CM_Model_Language $language = null, $deployVersion = null) {
        /** @var ResourceUrl $resourceUrl */
        $resourceUrl = parent::_create($url, null, $language, $deployVersion);
        $resourceUrl->setType($type);
        return $resourceUrl;
    }
}
