<?php

namespace CM\Url;

use CM_Http_Response_Resource_Javascript_ServiceWorker as HttpResponseServiceWorker;
use CM_Frontend_Environment;

class ServiceWorkerUrl extends AppUrl {

    public function __construct() {
        parent::__construct('');
    }

    public function getSegments() {
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
        return $segments;
    }

    /**
     * @param CM_Frontend_Environment|null $environment
     * @param string|null                  $deployVersion
     * @return ServiceWorkerUrl
     */
    public static function create(CM_Frontend_Environment $environment = null, $deployVersion = null) {
        /** @var ServiceWorkerUrl $url */
        $url = parent::createWithEnvironment('', $environment, $deployVersion);
        return $url;
    }
}
