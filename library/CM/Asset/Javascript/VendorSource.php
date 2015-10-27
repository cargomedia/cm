<?php

class CM_Asset_Javascript_VendorSource extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param string           $sourceRelativePath
     * @param bool|null        $generateSourceMap
     */
    public function __construct(CM_Site_Abstract $site, $sourceRelativePath, $generateSourceMap = null) {
        $generateSourceMap = (bool) $generateSourceMap;
        $sourceRelativePath = (string) $sourceRelativePath;

        $jsContainer = new CM_Frontend_JavascriptContainer();

        foreach (array_reverse($site->getModules()) as $moduleName) {
            $sourcePath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $sourceRelativePath;
            $sourceMainPaths = glob($sourcePath . '*/main.js');
            if (count($sourceMainPaths)) {
                $content = $this->_browserify($sourceMainPaths, $sourcePath, $generateSourceMap);
                $jsContainer->append($content);
            }
        }

        $this->_content = $jsContainer->compile();
    }
}
