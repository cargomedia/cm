<?php

class CM_Http_Response_Resource_Layout extends CM_Http_Response_Resource_Abstract {

    protected function _process() {
        $path = $this->getRequest()->getPath();
        $content = null;
        $mimeType = null;

        if ($pathRaw = $this->getRender()->getLayoutPath('resource/' . $path, null, null, true, false)) {
            $file = new CM_File($pathRaw);
            if (in_array($file->getExtension(), $this->_getFiletypesForbidden())) {
                throw new CM_Exception_Nonexistent('Forbidden filetype', CM_Exception::WARN, ['path' => $path]);
            }
            $content = $file->read();
            $mimeType = $file->getMimeType();
        } elseif ($pathTpl = $this->getRender()->getLayoutPath('resource/' . $path . '.smarty', null, null, true, false)) {
            $content = $this->getRender()->fetchTemplate($pathTpl);
            $mimeType = CM_File::getMimeTypeByContent($content);
        } else {
            throw new CM_Exception_Nonexistent('Invalid filename', CM_Exception::WARN, ['path' => $path]);
        }
        $this->setHeader('Content-Type', $mimeType);
        $this->_setContent($content);
    }

    /**
     * @return string[]
     */
    protected function _getFiletypesForbidden() {
        return ['smarty'];
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('layout')) {
            $request = clone $request;
            $url = $request->getUrl()->dropPathSegment('layout');
            $request->setUrl($url);
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
