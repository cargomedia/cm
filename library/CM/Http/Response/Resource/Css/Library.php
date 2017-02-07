<?php

class CM_Http_Response_Resource_Css_Library extends CM_Http_Response_Resource_Css_Abstract {

    protected function _process() {
        switch ($this->getRequest()->getPath()) {
            case '/all.css':
                $this->_setAsset(new CM_Asset_Css_Library($this->getRender()));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
        }
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'library-css') {
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
