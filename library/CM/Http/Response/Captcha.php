<?php

class CM_Http_Response_Captcha extends CM_Http_Response_Abstract {

    protected function _process() {
        $params = $this->_request->getQuery();
        $captcha = new CM_Captcha($params['id']);

        $this->setHeader('Content-Type', 'image/png');
        $this->_setContent($captcha->render(200, 40));
    }
    
    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Service_Manager $serviceManager) {
        $request = clone $request;
        if ($request->popPathPart(0) === 'captcha') {
            $request->popPathLanguage();
            $site = $request->popPathSite();
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
