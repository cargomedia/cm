<?php

class CM_Http_Response_Resource_Css_Library extends CM_Http_Response_Resource_Css_Abstract {

    protected function _process() {
        $url = $this->getRequest()->getUrl();
        switch (true) {
            case $url->matchPath('/all.css'):
                $this->_setAsset(new CM_Asset_Css_Library($this->getRender()));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
        }
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('library-css')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}
