<?php

class CM_Http_Response_Resource_Css_Library extends CM_Http_Response_Resource_Css_Abstract {

    protected function _process() {
        switch ($this->getRequest()->getPath()) {
            case '/all.css':
                $this->_setAsset(new CM_Asset_Css_Library($this->getRender()));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
        }
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $request = clone $request;
        if ($request->popPathPart(0) === 'library-css') {
            $request->popPathLanguage();
            $site = $request->popPathSite();
            $deployVersion = $request->popPathPart(0);
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
