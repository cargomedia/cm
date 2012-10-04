<?php

class CM_Response_Longpolling extends CM_Response_Abstract {
	
	/**
	 * @return string JSON
	 */
	public function process() {
		$params = CM_Params::factory($this->_request->getQuery());
		$channel = $params->getString('channel');

		$idMin = null;

		if ($this->getRequest()->hasHeader('If-None-Match')) {
			$idMin = $this->getRequest()->getHeader('If-None-Match');
		}

		$data = CM_Stream::subscribe($channel, $idMin);

		$return = null;

		if (!$data) {
			return null;
		}

		$this->_setHeader('ETag', $data['id']);
		$this->_setContent($data['data']);
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'longpolling';
	}
}
