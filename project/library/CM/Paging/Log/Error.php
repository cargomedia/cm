<?php

class CM_Paging_Log_Error extends CM_Paging_Log_Abstract {
	const TYPE = 1;

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$metaInfo = array();
		$request = CM_Request_Abstract::findInstance();
		if ($request && $request->hasSession() && $request->getSession()->getUser()) {
			$metaInfo['userId'] = $request->getSession()->getUser()->getId();
		}
		if (CM_Request_Abstract::getIp()) {
			$metaInfo['ip'] = CM_Request_Abstract::getIp();
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
		$this->_add($msg, $metaInfo);
	}
}
