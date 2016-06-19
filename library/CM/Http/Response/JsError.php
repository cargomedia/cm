<?php

class CM_Http_Response_JsError extends CM_Http_Response_Abstract {

    protected function _process() {
        $request = $this->getRequest();
        $query = $request->getQuery();

        $counter = (int) $query['counter'];
        $url = (string) $query['url'];
        $message = (string) $query['message'];
        $fileUrl = (string) $query['fileUrl'];
        $fileLine = (int) $query['fileLine'];

        $suppressLogging = $request->isBotCrawler() || !$request->isSupported();
        if (!$suppressLogging) {
            $exception = new CM_Exception_Javascript($message, $url, $counter, $fileUrl, $fileLine);
            $context = new CM_Log_Context();
            $context->setExtra(['type' => CM_Paging_Log_Javascript::getTypeStatic()]);
            $context->setException($exception);
            $this->getServiceManager()->getLogger()->warning('Javascript error', $context);
        }

        $this->setHeader('Content-Type', 'text/javascript');
        $this->_setContent('');
    }

    public static function createFromRequest(CM_Http_Request_Abstract $request, CM_Site_Abstract $site, CM_Service_Manager $serviceManager) {
        if ($request->getPathPart(0) === 'jserror') {
            $request = clone $request;
            $request->popPathPart(0);
            $request->popPathLanguage();
            $site = $request->popPathSite();
            return new self($request, $site, $serviceManager);
        }
        return null;
    }

}
