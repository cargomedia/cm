<?php

require_once 'AWSSDKforPHP/sdk.class.php';

class CM_Amazon_S3 extends CM_Class_Abstract {

	/** @var AmazonS3 */
	private $_sdk;

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
		$this->_sdk = new AmazonS3();
	}

	/**
	 * @param CM_File $file
	 * @param string  $bucketName
	 * @param string  $targetFilename
	 * @param array   $permissions
	 * @throws CM_Exception_Invalid
	 */
	public function createObject(CM_File $file, $bucketName, $targetFilename, array $permissions = null) {
		$bucketName = (string) $bucketName;
		if (!$bucketName) {
			throw new CM_Exception_Invalid('Bucket name cannot be empty');
		}
		$targetFilename = (string) $targetFilename;
		if (!$bucketName) {
			throw new CM_Exception_Invalid('Target filename cannot be empty');
		}
		$permissions = (array) $permissions;
		$acl = array();
		foreach ($permissions as $user => $permission) {
			$acl[] = array('id' => $user, 'permission' => $permission);
		}

		$response = $this->_sdk->create_object($bucketName, $targetFilename, array('fileUpload' => $file->getPath(), 'headers' => array(),
			'acl' => $acl));
		if (!$response->isOK()) {
			throw new CM_Exception_Invalid('Cannot upload file to S3 bucket `' . $bucketName . '`');
		}
	}
}
