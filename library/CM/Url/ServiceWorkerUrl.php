<?php

namespace CM\Url;

use CM_Frontend_Environment;

class ServiceWorkerUrl extends AbstractUrl {

    /** @var string */
    protected $_name;

    /** @var string|null */
    protected $_deployVersion = null;

    public function __construct($name = null) {
        parent::__construct('');
        if (null === $name) {
            $name = 'serviceworker';
        }
        $this->setName($name);
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
        return $this->getPathFromSegments($segments) . $this->getQueryComponent() . $this->getFragmentComponent();
    }

    /**
     * @param string|null                  $name
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return ServiceWorkerUrl
     */
    public static function create($name = null, CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var ServiceWorkerUrl $url */
        $url = parent::_create($name, $environment);
        $url->setDeployVersion($deployVersion);
        return $url;
    }
}
