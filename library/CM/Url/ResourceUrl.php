<?php

namespace CM\Url;

use CM_Site_Abstract;
use CM_Frontend_Environment;

class ResourceUrl extends AppUrl {

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

    public function withSite(CM_Site_Abstract $site) {
        $url = clone $this;
        $url->_site = $site;
        return $url->withBaseUrl($site->getUrlCdn());
    }

    protected function _getParameterSegments() {
        $segments = parent::_getParameterSegments();
        $segments[] = $this->getType();
        return $segments;
    }

    /**
     * @param string                       $url
     * @param string                       $type
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return ResourceUrl
     */
    public static function create($url, $type, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var ResourceUrl $resourceUrl */
        $resourceUrl = parent::createWithEnvironment($url, $environment, $deployVersion);
        $resourceUrl->setType($type);
        return $resourceUrl;
    }
}
