<?php

class CM_Asset_Css_Vendor extends CM_Asset_Css {

    public function __construct(CM_Frontend_Render $render) {
        parent::__construct($render);
        $extensions = array('css', 'less');
        foreach (array_reverse($render->getSite()->getModules()) as $moduleName) {
            $libraryPath = DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'client-vendor/';
            foreach ($extensions as $extension) {
                foreach (CM_Util::rglob('*.' . $extension, $libraryPath) as $path) {
                    $file = new CM_File($path);
                    $this->add($file->read());
                }
            }
        }
    }
}
