<?php

class CM_Http_Response_JsError extends CM_Http_Response_Abstract {

    protected function _process() {
        $request = $this->getRequest();
        if (!$request->isBotCrawler() && $request->isSupported()) {
            $query = $request->getQuery();
            if (!isset($query['error']) || !isset($query['error']['message'])) {
                throw new CM_Exception_Invalid('Failed to process a JS Error, "error.message" expected', CM_Exception::WARN, [
                    'query' => $query
                ]);
            }
            $context = new CM_Log_Context();
            $context->setExtra(array_merge($query, [
                'type' => CM_Paging_Log_Javascript::getTypeStatic(),
            ]));
            $this->getServiceManager()->getLogger()->warning('JS Error - ' . $query['error']['message'], $context);
        }
        $this->setHeader('Content-Type', 'application/json');
        $this->_setContent(json_encode(['status' => 'ok']));
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getUrl()->matchPath('jserror')) {
            $request = clone $request;
            return new self($request, $site, $serviceManager);
        }
        return null;
    }
}
