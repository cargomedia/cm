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
			$metaInfo['path'] = $request->getPath();
			if ($viewer = $request->getViewer()) {
				$metaInfo['userId'] = $viewer->getId();
			}
			if ($ip = $request->getIp()) {
				$metaInfo['ip'] = $ip;
			}
			if ($request->hasHeader('Referer')) {
				$metaInfo['referer'] = $request->getHeader('Referer');
			}
			if ($request->hasHeader('User-Agent')) {
				$metaInfo['useragent'] = $request->getHeader('User-Agent');
			}
		}
		$this->_add($msg, $metaInfo);
	}
}
