<?php

namespace CM\Url;

use CM_Exception_Invalid;
use CM_Model_Language;
use CM_Frontend_Environment;
use CM_Http_Response_Resource_Javascript_ServiceWorker as HttpResponseServiceWorker;

class ServiceWorkerUrl extends AppUrl {

    const PATTERN = '/\/(?P<service>' . HttpResponseServiceWorker::PATH_PREFIX_FILENAME .
    ')(?:\-(?P<lang>[a-z]+))?(?:\-(?<version>\d+))?\.js(?:[?#].*)?$/i';

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
     * @param string $uri
     * @return static
     * @throws CM_Exception_Invalid
     */
    public static function createFromString($uri) {
        $params = [];
        if (!self::matchUri($uri, $params)) {
            throw new CM_Exception_Invalid('Invalid serviceworker uri', null, [
                'uri' => $uri,
            ]);
        }

        /** @var ServiceWorkerUrl $url */
        $url = new static();
        $appUrl = AppUrl::createFromString($uri);
        if ($site = $appUrl->getSite()) {
            $url = $url->withSite($site);
        }
        if (isset($params['version'])) {
            $url->setDeployVersion((int) $params['version']);
        }
        if (isset($params['lang']) && $language = CM_Model_Language::findByAbbreviation($params['lang'])) {
            $url = $url->withLanguage($language);
        }
        return $url;
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

    /**
     * @param string     $uri
     * @param array|null $matches
     * @return bool
     */
    public static function matchUri($uri, array &$matches = null) {
        $matches = (array) $matches;
        return !!preg_match(self::PATTERN, $uri, $matches);
    }
}
