<?php

abstract class CMService_Amazon_Abstract extends CM_Class_Abstract {

	public function __construct() {
		$accessKey = self::_getConfig()->accessKey;
		if (!$accessKey) {
			throw new CM_Exception_Invalid('Amazon S3 `accessKey` not set');
		}
		$secretKey = self::_getConfig()->secretKey;
		if (!$secretKey) {
			throw new CM_Exception_Invalid('Amazon S3 `secretKey` not set');
		}
		CFCredentials::set(array('development' => array('key' => $accessKey, 'secret' => $secretKey, 'default_cache_config' => '',
			'certificate_authority' => false), '@default' => 'development'));
	}
}
