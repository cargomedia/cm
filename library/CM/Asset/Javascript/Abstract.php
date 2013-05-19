<?php

class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

	protected $_content;

	/**
	 * @param string $content
	 * @return string
	 */
	protected function _minify($content) {
		$md5 = md5($content);
		$cacheKey = CM_CacheConst::App_Resource . '_md5:' . $md5;
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

	/**
	 * @param boolean|null $minify
	 * @return string
	 */
	public function get($minify = null) {
		$content = $this->_content;
		if ($minify) {
			$content = $this->_minify($content);
		}
		return $content;
	}
}
