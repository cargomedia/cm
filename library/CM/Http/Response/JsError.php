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
            $this->getServiceManager()->getLogger()->addException($exception, CM_Log_Logger::WARNING);
        }

        $this->setHeader('Content-Type', 'text/javascript');
        $this->_setContent('');
    }

    public static function match(CM_Http_Request_Abstract $request) {
        return $request->getPathPart(0) === 'jserror';
    }
}
