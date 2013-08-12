<?php

class CM_CacheLocal extends CM_Cache_Apc {

	protected static function _getConfig() {
		static $config = false;
		if (false === $config) {
			$config = self::_getConfigRaw();
		}
		return $config;
	}
}
