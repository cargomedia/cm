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

		$this->setHeader('ETag', $data['id']);
		return $data['data'];
	}
}
