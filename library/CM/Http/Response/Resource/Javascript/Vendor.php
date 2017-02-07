<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();
        $site = $this->getSite();

        switch ($this->getRequest()->getPath()) {
            case '/before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug));
                break;
            case '/after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug));
                break;

            case '/dist-before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug, 'dist'));
                break;
            case '/dist-after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug, 'dist'));
                break;
            case '/source-before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug, 'source'));
                break;
            case '/source-after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug, 'source'));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
        }
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'vendor-js') {
            $request = clone $request;
            $request->popPathPart(0);
            $request->popPathLanguage();
            $site = $request->popPathSite();
            $deployVersion = $request->popPathPart(0);
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
