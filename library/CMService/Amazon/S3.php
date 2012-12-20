<?php

class CMService_Amazon_S3 extends CMService_Amazon_Abstract {

	/** @var AmazonS3 */
	private $_sdk;

	public function __construct() {
		parent::__construct();
		$this->_sdk = new AmazonS3();
	}

	/**
	 * @param CM_File      $file
	 * @param string       $bucketName
	 * @param string       $targetFilename
	 * @param array|null   $permissions
	 * @throws CM_Exception_Invalid
	 */
	public function upload(CM_File $file, $bucketName, $targetFilename, array $permissions = null) {
		$bucketName = (string) $bucketName;
		if (!strlen($bucketName)) {
			throw new CM_Exception_Invalid('Bucket name cannot be empty');
		}
		$targetFilename = (string) $targetFilename;
		if (!strlen($targetFilename)) {
			throw new CM_Exception_Invalid('Target filename cannot be empty');
		}
		$permissions = (array) $permissions;
		$acl = array();
		foreach ($permissions as $user => $permission) {
			$acl[] = array('id' => (string) $user, 'permission' => (string) $permission);
		}

		$response = $this->_sdk->create_object($bucketName, $targetFilename, array('fileUpload' => $file->getPath(), 'headers' => array(),
			'acl' => $acl));
		if (!$response->isOK()) {
			throw new CM_Exception_Invalid('Cannot upload file to S3 bucket `' . $bucketName . '`');
		}
	}
}
