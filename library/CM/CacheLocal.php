<?php

class CM_CacheLocal extends CM_Cache_Apc {

	protected function _delete($key) {
		throw new CM_Exception_NotAllowed('Cannot delete keys on local cache');
	}

	public static function cleanLanguages() {
		self::flush();
	}
}
