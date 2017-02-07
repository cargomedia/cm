<?php

abstract class CM_Http_Response_Resource_Abstract extends CM_Http_Response_Abstract {

    protected function _setContent($content) {
        $this->setHeader('Access-Control-Allow-Origin', $this->getSite()->getUrl()->withoutPrefix()->getUriBaseComponents());
        $this->setHeaderExpires(86400 * 365);

        parent::_setContent($content);
    }
}
