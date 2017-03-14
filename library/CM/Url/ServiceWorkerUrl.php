<?php

namespace CM\Url;

use CM_Http_Response_Resource_Javascript_ServiceWorker as HttpResponseServiceWorker;
use CM_Frontend_Environment;

class ServiceWorkerUrl extends AbstractUrl {

    /** @var string|null */
    protected $_deployVersion = null;

    public function __construct() {
        parent::__construct('');
        $this->_trailingSlash = false;
    }

    /**
     * @return string|null
     */
    public function getDeployVersion() {
        return $this->_deployVersion;
    }

    /**
     * @param string|null $deployVersion
     */
    public function setDeployVersion($deployVersion) {
        $this->_deployVersion = $deployVersion;
    }

    public function getUriRelativeComponents() {
        $parts = [
            HttpResponseServiceWorker::PATH_PREFIX_FILENAME,
        ];
        if ($language = $this->getLanguage()) {
            $parts[] = $language->getAbbreviation();
        }
        if ($deployVersion = $this->getDeployVersion()) {
            $parts[] = $deployVersion;
        }

        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        $segments[] = sprintf('%s.js', implode('-', $parts));
        return $this->_getPathFromSegments($segments) . $this->_getQueryComponent() . $this->_getFragmentComponent();
    }

    /**
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return ServiceWorkerUrl
     */
    public static function create(CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var ServiceWorkerUrl $url */
        $url = parent::_create('', $environment);
        $url->setDeployVersion($deployVersion);
        return $url;
    }
}
