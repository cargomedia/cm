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
		if (false === ($contentMinified = CM_Cache_File::get($cacheKey))) {
			$contentMinified = CM_Util::exec('uglifyjs --no-copyright', null, $content);
			CM_Cache_File::set($cacheKey, $contentMinified);
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
