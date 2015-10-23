<?php

class CM_Asset_Javascript_VendorBeforeBody extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool             $generateSourceMap
     */
    public function __construct(CM_Site_Abstract $site, $generateSourceMap = null) {
        $generateSourceMap = (bool) $generateSourceMap;
        $content = '';
        foreach (array_reverse($site->getModules()) as $moduleName) {
            $initPath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/before-body/';
            foreach (CM_Util::rglob('*.js', $initPath) as $path) {
                $content .= (new CM_File($path))->read() . ';' . PHP_EOL;
            }

            $sourcePath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/before-body-source/';
            foreach (glob($sourcePath . '*/main.js') as $path) {
                $content .= $this->_browserify($path, $generateSourceMap) . ';' . PHP_EOL;
            }
        }
        $this->_content = $content;
    }
}
