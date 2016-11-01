<?php

class CM_Http_Response_Resource_Javascript_Library extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        // TODO: define who can access to sourcemaps....
        $dev = true;
        $debug = $this->getEnvironment()->isDebug();
        $site = $this->getSite();
        $isSourceMapsRequest = preg_match('/\.map$/', $this->getRequest()->getPath());

        if (($debug || $dev) && !$isSourceMapsRequest) {
            $this->setHeader('X-SourceMap', $this->getRequest()->getUri() . '.map');
        }

        if (preg_match('/^\/library.js/', $this->getRequest()->getPath())) {
            $this->_setAsset(new CM_Asset_Javascript_Bundle_Library($site, $debug, $isSourceMapsRequest));
            return;
        }
        if ($this->getRequest()->getPathPart(0) === 'translations') {
            $language = $this->getRender()->getLanguage();
            if (!$language) {
                throw new CM_Exception_Invalid('Render has no language');
            }
            $this->_setAsset(new CM_Asset_Javascript_Bundle_Translations($language, $site, $debug, $isSourceMapsRequest));
            return;
        }
        throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'library-js') {
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
