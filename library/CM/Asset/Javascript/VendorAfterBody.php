<?php

class CM_Asset_Javascript_VendorAfterBody extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $generateSourceMap
     */
    public function __construct(CM_Site_Abstract $site, $generateSourceMap = null) {
        $generateSourceMap = (bool) $generateSourceMap;
        $jsContainer = new CM_Frontend_JavascriptContainer();

        foreach (array_reverse($site->getModules()) as $moduleName) {
            $initPath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/after-body/';
            foreach (CM_Util::rglob('*.js', $initPath) as $path) {
                $content = (new CM_File($path))->read();
                $jsContainer->append($content);
            }

            $sourcePath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/after-body-source/';
            $sourceMainPaths = glob($sourcePath . '*/main.js');
            if (count($sourceMainPaths)) {
                $content = $this->_browserify($sourceMainPaths, $sourcePath, $generateSourceMap);
                $jsContainer->append($content);
            }
        }

        $this->_content = $jsContainer->compile();
    }
}
