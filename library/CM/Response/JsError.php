<?php

class CM_Response_JsError extends CM_Response_Abstract {

	protected function _process() {
		$query = $this->_request->getQuery();
		$counter = (int) $query['counter'];
		$url = (string) $query['url'];
		$message = (string) $query['message'];
		$fileUrl = (string) $query['fileUrl'];
		$fileLine = (int) $query['fileLine'];

		$text = $message . PHP_EOL;
		$text .= '## ' . $fileUrl . '(' . $fileLine . ')' . PHP_EOL;

		$log = new CM_Paging_Log_JsError();
		$log->add($text, array('url' => $url, 'errorCounter' => $counter));

		$this->setHeader('Content-Type', 'text/javascript');
		$this->_setContent('');
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'jserror';
	}
}
