<?php

class CM_Asset_Javascript_Library extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        parent::__construct($site, $debug);

        foreach (self::getIncludedPaths($site) as $path) {
            $this->_js->append((new CM_File($path))->read());
        }

        $internal = new CM_Asset_Javascript_Internal($site, $debug);
        $this->_js->append($internal->get());
    }

    /**
     * @param CM_Site_Abstract $site
     * @return string[]
     */
    public static function getIncludedPaths(CM_Site_Abstract $site) {
        $pathsUnsorted = CM_Util::rglobLibraries('*.js', $site);
        return array_keys(CM_Util::getClasses($pathsUnsorted));
    }
}
