<?php

class CM_Http_Response_JsError extends CM_Http_Response_Abstract {

    protected function _process() {
        $request = $this->getRequest();
        if (!$request->isBotCrawler() && $request->isSupported()) {
            $query = $request->getQuery();
            $context = new CM_Log_Context();
            $context->setExtra($query);
            $this->getServiceManager()->getLogger()->warning('JS Error - ' . $query['error']['message'], $context);
        }
        $this->setHeader('Content-Type', 'application/json');
        $this->_setContent(json_encode(['status' => 'ok']));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'jserror') {
            $request = clone $request;
            $request->popPathPart(0);
            $request->popPathLanguage();
            return new self($request, $site, $serviceManager);
        }
        return null;
    }
}
