<?php

namespace CM\Url;

use CM_Model_Language;
use League\Uri\Components\HierarchicalPath;

class ServiceWorkerUrl extends AbstractUrl {

    /** @var string */
    protected $_name;

    /** @var string|null */
    protected $_deployVersion = null;

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

    protected function _getUriRelativeComponents() {
        $parts = [
            $this->getName(),
        ];
        if ($language = $this->getLanguage()) {
            $parts[] = $language->getAbbreviation();
        }
        if ($deployVersion = $this->getDeployVersion()) {
            $parts[] = $deployVersion;
        }

        $path = HierarchicalPath::createFromSegments([
            sprintf('%s.js', implode('-', $parts)),
        ], HierarchicalPath::IS_ABSOLUTE);

        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }

        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string|null            $name
     * @param UrlInterface|null      $baseUrl
     * @param CM_Model_Language|null $language
     * @param string|null            $deployVersion
     * @return ServiceWorkerUrl
     */
    public static function create($name = null, UrlInterface $baseUrl = null, CM_Model_Language $language = null, $deployVersion = null) {
        if (null === $name) {
            $name = 'serviceworker';
        }

        /** @var ServiceWorkerUrl $url */
        $url = parent::_create('', $baseUrl, $language);
        $url->setName($name);
        $url->setDeployVersion($deployVersion);
        return $url;
    }
}
