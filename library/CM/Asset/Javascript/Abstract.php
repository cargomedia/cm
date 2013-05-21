<?php

class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

	protected $_content;

	public function get($compress = null) {
		$content = (string) $this->_content;
		if ($compress) {
			$content = $this->_minify($content);
		}
		return $content;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function _minify($content) {
		$md5 = md5($content);
		$cacheKey = CM_CacheConst::App_Resource . '_md5:' . $md5;
		if (false === ($contentMinified = CM_Cache_File::get($cacheKey))) {
			$contentMinified = CM_Util::exec('uglifyjs --no-copyright', null, $content);
			CM_Cache_File::set($cacheKey, $contentMinified);
		}
		return $contentMinified;
	}
}
