<?php
class CM_Component_Debug extends CM_Component_Abstract {

	public function checkAccessible() {
		if (!IS_DEBUG) {
			throw new CM_Exception_NotAllowed();
		}
	}

	public function prepare() {
		$debug = CM_Debug::get();
		$stats = $debug->getStats();
		ksort($stats);
		$this->setTplParam('stats', $stats);
		$cacheNames = array('CM_Cache', 'CM_CacheLocal', 'CM_Cache_File');
		$this->_setJsParam('cacheNames', $cacheNames);
		$this->setTplParam('cacheNames', $cacheNames);
	}

	public function ajax_clearCache(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		$cachesCleared = array();
		if ($params->getBoolean('CM_Cache', false)) {
			CM_Cache::flush();
			$cachesCleared[] = 'CM_Cache';
		}
		if ($params->getBoolean('CM_CacheLocal', false)) {
			CM_CacheLocal::flush();
			$cachesCleared[] = 'CM_CacheLocal';
		}
		if ($params->getBoolean('CM_Cache_File', false)) {
			CM_Cache_File::flush();
			$cachesCleared[] = 'CM_Cache_File';
		}
		$handler->message('Cleared: ' . implode(', ', $cachesCleared));
	}

}
