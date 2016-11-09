<?php

abstract class CM_Http_Response_Resource_Javascript_Abstract extends CM_Http_Response_Resource_Abstract {

    /** @var bool */
    protected $_isSourceMaps;

    /** @var bool */
    protected $_withSourceMaps;

    public function __construct(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        $sourceMapsPart = $request->getPathPart(0);
        $this->_isSourceMaps = $sourceMapsPart === 'sourcemaps';
        $this->_withSourceMaps = $sourceMapsPart === 'with-sourcemaps';
        if ($this->_isSourceMaps || $this->_withSourceMaps) {
            $request->popPathPart(0);
        }
        parent::__construct($request, $site, $serviceManager);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function _getSourceMapsUrl($type) {
        return $this->getRender()->getUrlResource($type . '-js', 'sourcemaps' . $this->getRequest()->getPath());
    }

    protected function _setContent($content) {
        $this->setHeader('Content-Type', 'application/x-javascript');
        parent::_setContent($content);
    }

    /**
     * @param CM_Asset_Javascript_Abstract $resource
     */
    protected function _setAsset(CM_Asset_Javascript_Abstract $resource) {
        $this->_setContent($resource->get());
    }
}
