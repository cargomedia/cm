<?php

class CM_Http_Response_Resource_Javascript_Vendor extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();
        $site = $this->getSite();
        $url = $this->getRequest()->getUrl();
        switch (true) {
            case $url->matchPath('/before-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug));
                break;
            case $url->matchPath('/after-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug));
                break;

            case $url->matchPath('/dist-before-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug, 'dist'));
                break;
            case $url->matchPath('/dist-after-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug, 'dist'));
                break;
            case $url->matchPath('/source-before-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_BeforeBody($site, $debug, 'source'));
                break;
            case $url->matchPath('/source-after-body.js'):
                $this->_setAsset(new CM_Asset_Javascript_Vendor_AfterBody($site, $debug, 'source'));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
        }
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('vendor-js')) {
            $request = clone $request;
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
