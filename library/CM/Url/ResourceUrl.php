<?php

namespace CM\Url;

use League\Uri\Components\HierarchicalPath;

class ResourceUrl extends RelativeUrl {

    /** @var  string */
    private $_type;

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
        $this->_type = $type;
    }

    protected function _buildPath(\CM_Frontend_Environment $environment) {
        $urlPath = new HierarchicalPath(sprintf('/%s', $this->getType()));
        $site = $environment->getSite();
        $language = $environment->getLanguage();

        if ($language) {
            $urlPath = $urlPath->append($language->getAbbreviation());
        }

        return $urlPath
            ->append((string) $site->getId())
            ->append((string) \CM_App::getInstance()->getDeployVersion())
            ->append((string) $this->path);
    }

    /**
     * @param string|null $uri
     * @param string|null $type
     * @return ResourceUrl
     */
    public static function createFromString($uri = null, $type = null) {
        /** @var ResourceUrl $resourceUrl */
        $resourceUrl = parent::createFromString((string) $uri);
        $resourceUrl->setType((string) $type);
        return $resourceUrl;
    }
}
