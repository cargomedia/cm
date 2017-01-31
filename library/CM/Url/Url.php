<?php

namespace CM\Url;

use CM_Frontend_Environment;
use League\Uri\Components\HierarchicalPath;

class Url extends AbstractUrl {

    public function getUriRelativeComponents() {
        $path = clone $this->path;
        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @return AbstractUrl
     */
    public static function create($url, CM_Frontend_Environment $environment = null) {
        return parent::_create($url, $environment);
    }
}
