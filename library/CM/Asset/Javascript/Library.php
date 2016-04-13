<?php

class CM_Asset_Javascript_Library extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Site_Abstract $site) {
        $debug = CM_Bootloader::getInstance()->isDebug();
        $moduleFiles = [];
        $modulePaths = [];

        foreach($site->getModules() as $moduleName) {
            $moduleFiles = array_merge($moduleFiles, CM_Util::rglobLibrariesByModule('*.js', $moduleName));
            $modulePaths[] = CM_Util::getModulePath($moduleName, false, true) . '/library/';
        }

        $content = $this->_browserify($moduleFiles, $modulePaths, $debug, true);
        $internal = new CM_Asset_Javascript_Internal($site);
        $content .= $internal->get();
        $this->_content = $content;
    }
}
