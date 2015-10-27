<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = CM_Bootloader::getInstance()->isDebug();
        $path = $this->getRequest()->getPath();

        $asset = new CM_Asset_Javascript_Vendor($this->getSite());
        $matches = [];

        if($path == '/before-body.js') {
            $asset->compileJsForAllModules('client-vendor/before-body/');
            $asset->browserifyJsForAllModules('client-vendor/before-body-source/');
        }
        else if($path == '/after-body.js'){
            $asset->compileJsForAllModules('client-vendor/after-body/');
            $asset->browserifyJsForAllModules('client-vendor/after-body-source/');
        }
        else if($path == '/common-before-body.js'){
            $asset->compileJsForAllModules('client-vendor/before-body/');
        }
        else if($path == '/common-after-body.js'){
            $asset->compileJsForAllModules('client-vendor/after-body/');
        }
        else if(preg_match('/^\/([^-]+)-before-body.js$/', $path, $matches)){
            $module = $matches[1];
            $asset->browserifyJs($module, 'client-vendor/before-body-source/', $debug);
        }
        else if(preg_match('/^\/([^-]+)-after-body.js$/', $path, $matches)){
            $module = $matches[1];
            $asset->browserifyJs($module, 'client-vendor/after-body-source/', $debug);
        }
        else {
            throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
        }

        $this->_setAsset($asset);
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'vendor-js';
    }
}
