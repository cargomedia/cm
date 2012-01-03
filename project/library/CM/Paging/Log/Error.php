<?php

class CM_Paging_Log_Error extends CM_Paging_Log_Abstract {

	/**
	 * @return int
	 */
	public function getType() {
		return self::TYPE_ERROR;
	}

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$metaInfo = array();
		if (CM_Session::getInstance()->getUser()) {
			$metaInfo['userId'] = CM_Session::getInstance()->getUser()->getId();
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
