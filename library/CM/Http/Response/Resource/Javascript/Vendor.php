<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = CM_Bootloader::getInstance()->isDebug();

        switch ($this->getRequest()->getPath()) {
            case '/before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor($this->getSite(), 'client-vendor/before-body/'));
                break;
            case '/after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor($this->getSite(), 'client-vendor/after-body/'));
                break;
            case '/before-body-source.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorSource($this->getSite(), 'client-vendor/before-body-source/', $debug));
                break;
            case '/after-body-source.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorSource($this->getSite(), 'client-vendor/after-body-source/', $debug));
                break;

            default:
                throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
        }
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'vendor-js';
    }
}
