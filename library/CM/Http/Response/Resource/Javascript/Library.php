<?php

class CM_Http_Response_Resource_Javascript_Library extends CM_Http_Response_Resource_Javascript_Abstract {

    protected function _process() {
        $debug = $this->getEnvironment()->isDebug();
        $url = $this->getRequest()->getUrl();
        if ($url->matchPath('/library.js')) {
            $this->_setAsset(new CM_Asset_Javascript_Library($this->getSite(), $debug));
            return;
        }
        if ($url->matchPath('translations')) {
            $language = $this->getRender()->getLanguage();
            if (!$language) {
                throw new CM_Exception_Invalid('Render has no language');
            }
            $this->_setAsset(new CM_Asset_Javascript_Translations($this->getSite(), $debug, $language));
            return;
        }
        throw new CM_Exception_Invalid('Invalid path provided', CM_Exception::WARN, ['path' => $this->getRequest()->getPath()]);
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('library-js')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}
