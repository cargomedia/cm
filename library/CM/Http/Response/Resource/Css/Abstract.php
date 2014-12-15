<?php

abstract class CM_Http_Response_Resource_Css_Abstract extends CM_Http_Response_Resource_Abstract {

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
}
