<?php

class CM_Http_Response_Captcha extends CM_Http_Response_Abstract {

    protected function _process() {
        $params = $this->_request->getQuery();
        $captcha = new CM_Captcha($params['id']);

        $this->setHeader('Content-Type', 'image/png');
        $this->_setContent($captcha->render(200, 40));
    }
    
    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'captcha') {
            $request = clone $request;
            $request->popPathPart(0);
            $request->popPathLanguage();
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
