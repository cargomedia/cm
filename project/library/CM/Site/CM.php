<?php

class CM_Site_CM extends CM_Site_Abstract {
	const TYPE = 1;

	/**
	 * @return string
	 */
	public function getUrl() {
		return URL_ROOT;
	}

	public function getUrlCdn() {
		return URL_OBJECTS;
	}
}
