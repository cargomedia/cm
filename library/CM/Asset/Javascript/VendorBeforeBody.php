<?php

class CM_Asset_Javascript_VendorBeforeBody extends CM_Asset_Javascript_Abstract {

    public function __construct(CM_Site_Abstract $site) {
        $content = '';
        foreach (array_reverse($site->getModules()) as $moduleName) {
            $initPath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/before-body/';
            foreach (CM_Util::rglob('*.js', $initPath) as $path) {
                $content .= (new CM_File($path))->read() . ';' . PHP_EOL;
            }
        }
        $this->_content = $content;
    }
}
