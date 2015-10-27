<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = CM_Bootloader::getInstance()->isDebug();
        $site = $this->getSite();

        switch ($this->getRequest()->getPath()) {
            case '/before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorBeforeBody($site));
                break;
            case '/after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorAfterBody($site));
                break;

            case '/dist-before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorBeforeBody($site, 'dist', $debug));
                break;
            case '/dist-after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorAfterBody($site, 'dist', $debug));
                break;
            case '/source-before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorBeforeBody($site, 'source', $debug));
                break;
            case '/source-after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_VendorAfterBody($site, 'source', $debug));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
        }
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'vendor-js';
    }
}
