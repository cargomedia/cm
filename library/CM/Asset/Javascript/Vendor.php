<?php

class CM_Asset_Javascript_Vendor extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param string           $sourceRelativePath
     *
     */
    public function __construct(CM_Site_Abstract $site, $sourceRelativePath) {
        $sourceRelativePath = (string) $sourceRelativePath;
        $jsContainer = new CM_Frontend_JavascriptContainer();

        foreach (array_reverse($site->getModules()) as $moduleName) {
            $initPath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $sourceRelativePath;
            foreach (CM_Util::rglob('*.js', $initPath) as $path) {
                $content = (new CM_File($path))->read();
                $jsContainer->append($content);
            }
        }

        $this->_content = $jsContainer->compile();
    }
}
