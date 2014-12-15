<?php

class CM_Http_Response_Resource_Css extends CM_Http_Response_Resource_Abstract {

    protected function _process() {
        switch ($this->getRequest()->getPath()) {
            case '/library.css':
                $this->_setAsset(new CM_Asset_Css_Library($this->getRender()));
                break;
            case '/vendor.css':
                $this->_setAsset(new CM_Asset_Css_Vendor($this->getRender()));
                break;
            default:
                throw new CM_Exception_Invalid('Invalid path `' . $this->getRequest()->getPath() . '` provided', null, array('severity' => CM_Exception::WARN));
        }
    }

    protected function _setContent($content) {
        $this->setHeader('Content-Type', 'text/css');
        parent::_setContent($content);
    }

    /**
     * @param CM_Asset_Css $asset
     */
    protected function _setAsset(CM_Asset_Css $asset) {
        $compress = !CM_Bootloader::getInstance()->isDebug();
        $this->_setContent($asset->get($compress));
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'css';
    }
}
