<?php

class CM_Response_JsError extends CM_Response_Abstract {

	protected function _process() {
		$query = $this->_request->getQuery();
		$index = (int) $query['index'];
		$url = (string) $query['url'];
		$message = (string) $query['message'];
		$fileUrl = (string) $query['fileUrl'];
		$fileLine = (int) $query['fileLine'];

		$text = 'Error #' . $index . ' : ' . $message . PHP_EOL;
		$text .= 'URL: ' . $url . PHP_EOL;
		$text .= 'File: ' . $fileUrl . ' (line ' . $fileLine . ')' . PHP_EOL;

		$log = new CM_Paging_Log_JsError();
		$log->add($text);

		$this->setHeader('Content-Type', 'text/javascript');
		$this->_setContent('');
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'jserror';
	}
}
