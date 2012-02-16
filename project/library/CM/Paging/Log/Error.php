<?php

class CM_Paging_Log_Error extends CM_Paging_Log_Abstract {
	const TYPE = 1;

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$metaInfo = array();
		if (CM_Request_Abstract::hasInstance()) {
			$request = CM_Request_Abstract::getInstance();
			if ($viewer = $request->getViewer()) {
				$metaInfo['userId'] = $viewer->getId();
			}
			if ($ip = $request->getIp()) {
				$metaInfo['ip'] = $ip;
			}
			if (isset($_SERVER['REQUEST_URI'])) {
				$metaInfo['uri'] = $_SERVER['REQUEST_URI'];
			}
			if (isset($_SERVER['HTTP_REFERER'])) {
				$metaInfo['referer'] = $_SERVER['HTTP_REFERER'];
			}
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$metaInfo['useragent'] = $_SERVER['HTTP_USER_AGENT'];
			}
		}
		$this->_add($msg, $metaInfo);
	}
}
