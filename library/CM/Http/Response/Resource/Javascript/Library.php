<?php

class CM_Http_Response_Resource_Javascript_Library extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();

        if ($this->getRequest()->getPath() === '/library.js') {
            $this->_setAsset(new CM_Asset_Javascript_Library($this->getSite(), $debug));
            return;
        }
        if ($this->getRequest()->getPathPart(0) === 'translations') {
            $language = $this->getRender()->getLanguage();
            if (!$language) {
                throw new CM_Exception_Invalid('Render has no language');
            }
            $this->_setAsset(new CM_Asset_Javascript_Translations($this->getSite(), $debug, $language));
            return;
        }
        throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', CM_Exception::WARN);
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $request = clone $request;
        if ($request->popPathPart(0) === 'library-js') {
            $request->popPathLanguage();
            $site = $request->popPathSite();
            $deployVersion = $request->popPathPart(0);
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
