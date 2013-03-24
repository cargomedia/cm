<?php

abstract class CM_Response_Resource_Javascript_Abstract extends CM_Response_Resource_Abstract {

	protected function _setContent($content) {
		$this->enableCache();
		$this->setHeader('Content-Type', 'application/x-javascript');
		if (!$this->getRender()->isDebug()) {
			$content = $this->_minify($content);
		}
		parent::_setContent($content);
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function _minify($content) {
		$md5 = md5($content);
		$cacheKey = CM_CacheConst::Response_Resource_JS . '_md5:' . $md5;
		if (false === ($contentMinified = CM_CacheLocal::get($cacheKey))) {
			$lock = new CM_Lock($cacheKey);
			$lock->waitUntilUnlocked();

			if (false === ($contentMinified = CM_CacheLocal::get($cacheKey))) {
				$lock->lock();
				$contentMinified = CM_Util::exec('uglifyjs --no-copyright', null, $content);
				CM_CacheLocal::set($cacheKey, $contentMinified);
				$lock->unlock();
			}
		}
		return $contentMinified;
	}
}
