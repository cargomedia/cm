<?php

class CM_Http_Response_Captcha extends CM_Http_Response_Abstract {

    protected function _process() {
        $params = $this->_request->getQuery();
        if (!isset($params['id'])) {
            throw new CM_Exception_InvalidParam('"Id" param is not set', CM_Exception::WARN);
        }
        $captcha = new CM_Captcha($params['id']);

        $this->setHeader('Content-Type', 'image/png');
        $this->_setContent($captcha->render(200, 40));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('captcha')) {
            $request = clone $request;
            return new self($request, $request->getSite(), $serviceManager);
        }
        return null;
    }

}
