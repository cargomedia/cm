<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = CM_Bootloader::getInstance()->isDebug();
        $asset = new CM_Asset_Javascript_Vendor($this->getSite());

        switch ($this->getRequest()->getPath()) {

            case '/before-body.js':
                $asset->mergeJs('client-vendor/before-body/');
                $asset->browserifyJs('client-vendor/before-body-source/');
                break;
            case '/after-body.js':
                $asset->mergeJs('client-vendor/after-body/');
                $asset->browserifyJs('client-vendor/after-body-source/');
                break;

            case '/merged-before-body.js':
                $asset->mergeJs('client-vendor/before-body/');
                break;
            case '/merged-after-body.js':
                $asset->mergeJs('client-vendor/after-body/');
                break;
            case '/source-before-body.js':
                $asset->browserifyJs('client-vendor/before-body-source/', $debug);
                break;
            case '/source-after-body.js':
                $asset->browserifyJs('client-vendor/after-body-source/', $debug);
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
        }

        $this->_setAsset($asset);
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'vendor-js';
    }
}
