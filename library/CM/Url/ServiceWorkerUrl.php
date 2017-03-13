<?php

namespace CM\Url;

use CM_Http_Response_Resource_Javascript_ServiceWorker as HttpResponseServiceWorker;
use CM_Frontend_Environment;

class ServiceWorkerUrl extends AbstractUrl {

    /** @var string */
    protected $_name;

    /** @var string|null */
    protected $_deployVersion = null;

    /**
     * @param string $name
     */
    public function __construct($name) {
        parent::__construct('');
        $this->setName($name);
        $this->_trailingSlash = false;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->_name = (string) $name;
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
            $this->getName(),
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
        $url = parent::_create(HttpResponseServiceWorker::PATH_PREFIX_FILENAME, $environment);
        $url->setDeployVersion($deployVersion);
        return $url;
    }
}
