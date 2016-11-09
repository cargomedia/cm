<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $user = $this->getRequest()->getSession()->getUser();
        // TODO: define who can access to sourcemaps....
        $dev = true;
        $site = $this->getSite();

        switch ($this->getRequest()->getPath()) {
            case '/before-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Bundle_Vendor_BeforeBody($site));
                if ($dev) {
                    $this->setHeader('X-SourceMap', $this->getRequest()->getUri() . '.map');
                }
                break;
            case '/after-body.js':
                $this->_setAsset(new CM_Asset_Javascript_Bundle_Vendor_AfterBody($site));
                if ($dev) {
                    $this->setHeader('X-SourceMap', $this->getRequest()->getUri() . '.map');
                }
                break;
            case '/before-body.js.map':
                if ($dev) {
                    $this->_setAsset(new CM_Asset_Javascript_Bundle_Vendor_BeforeBody($site, true));
                }
                break;
            case '/after-body.js.map':
                if ($dev) {
                    $this->_setAsset(new CM_Asset_Javascript_Bundle_Vendor_AfterBody($site, true));
                }
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
