<?php

class CM_Response_Resource_Layout extends CM_Response_Resource_Abstract {

    protected function _process() {
        $content = null;
        $mimeType = null;

        if ($pathRaw = $this->getRender()->getLayoutPath('resource/' . $this->getRequest()->getPath(), null, true, false)) {
            $file = new CM_File($pathRaw);
            $content = $file->read();
            $mimeType = $file->getMimeType();
        } elseif ($pathTpl = $this->getRender()->getLayoutPath('resource/' . $this->getRequest()->getPath() . '.smarty', null, true, false)) {
            $content = $this->getRender()->fetchTemplate($pathTpl);
            $mimeType = CM_File::getMimeTypeByContent($content);
        } else {
            throw new CM_Exception_Nonexistent('Invalid filename: `' . $this->getRequest()->getPath() . '`', null,
                array('severity' => CM_Exception::WARN));
        }
        $this->enableCache();
        $this->setHeader('Content-Type', $mimeType);
        $this->_setContent($content);
    }

    public static function match(CM_Request_Abstract $request) {
        return $request->getPathPart(0) === 'layout';
    }
}
