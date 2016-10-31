<?php

class CM_Asset_Javascript_Bundle_Library extends CM_Asset_Javascript_Bundle_Abstract {

    public function __construct(CM_Site_Abstract $site, $debug = null, $sourceMapsOnly = null) {
        parent::__construct($site, $debug, $sourceMapsOnly);

        foreach (self::getIncludedPaths($site) as $path) {
            $this->_js->addRawPath($path);
        }

        $watch = [];
        $mapping = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $mapping['/' . $moduleName . '/library/'] = '.*/' . $moduleName . '/library/' . $moduleName;
            $watch[] = CM_Util::getModulePath($moduleName) . '/library/**/*.js';
        }
        $this->_js->addSourceMapping($mapping);
        $this->_js->addWatchPaths($watch);

        $internal = new CM_Asset_Javascript_Internal($site, $debug);
        $this->_js->addInlineContent('internals', $internal->get(), false, true);
    }

    protected function _getBundleName() {
        return 'library.js';
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
