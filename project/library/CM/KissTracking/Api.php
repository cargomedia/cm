<?php

class CM_KissTracking_Api extends CM_Class_Abstract {

	const setName = 'kisstracking';

	/** @var CM_KissTracking_Api */
	private static $_instance;

	/** @var CM_Set */
	private $_set;

	/**
	 * @param String        $event
	 * @param CM_Model_User $user
	 * @param null|String   $alias
	 * @param array         $properties
	 * @return CM_KissTracking_Api
	 */
	public function trackUser($event, CM_Model_User $user, $alias = null, array $properties = null) {
		return $this->track($event, $user->getId(), $alias, $properties);
	}

	/**
	 * @param       $event
	 * @param       $identity
	 * @param null  $alias
	 * @param array $properties
	 * @return CM_KissTracking_Api
	 */
	public function track($event, $identity, $alias = null, array $properties = null) {
		$event = (string) $event;
		$identity = (string) $identity;
		if (null !== $alias) {
			$alias = (string) $alias;
		}
		$properties = (array) $properties;
		$record = array('Identity' => $identity, 'Alias' => $alias);
		$record['Timestamp'] = (string) time();
		$record['Event'] = $event;
		foreach ($properties as $propName => &$propValue) {
			$record['Prop:' . $propName] = (string) $propValue;
		}

		$this->_getSet()->add($record);
		return $this;
	}

	public function exportEvents() {
		$csvFile = $this->generateCsv();
		$this->_uploadCsv($csvFile);
	}

	/**
	 * @return CM_File_Csv
	 */
	public function generateCsv() {
		$filename = $this->_getFileName();
		$records = $this->_getEvents();
		$header = array();
		foreach ($records as $record) {
			foreach ($record as $key => $value) {
				$header[$key] = $key;
			}
		}

		if (!file_exists($filename) || !is_readable($filename)) {
			$file = CM_File_Csv::create($filename);
			$file->appendRow($header);
		} else {
			$file = new CM_File_Csv($filename);
			$file->mergeHeader($header);
		}

		$headerMap = array_fill_keys($file->getHeader(), null);
		foreach ($records as $record) {
			$record = array_merge($headerMap, $record);
			$file->appendRow($record);
		}
		return $file;
	}

	/**
	 * @return string
	 */
	public function _getFileName() {
		return DIR_TMP . self::_getConfig()->csvFile;
	}

	/**
	 * @return CM_Set
	 */
	private function _getSet() {
		if (!$this->_set instanceof CM_Set) {
			$this->_set = new CM_Set(self::setName);
		}
		return $this->_set;
	}

	/**
	 * @return mixed[]
	 */
	private function _getEvents() {
		return $this->_getSet()->flush();
	}

	/**
	 * @param CM_File_Csv $file
	 */
	private function _uploadCsv(CM_File_Csv $file) {
		$accessKey = self::_getConfig()->awsAccessKey;
		$secretKey = self::_getConfig()->awsSecretKey;
		$bucketName = self::_getConfig()->awsBucketName;
		$targetFilename = self::_getConfig()->s3FilePrefix . '-' . date('YmdHis');

		require_once DIR_LIBRARY . 'amazon-s3-php-class/S3.php';
		$s3 = new S3($accessKey, $secretKey);
		$s3->putObjectFile($file->getPath(), $bucketName, $targetFilename, S3::ACL_AUTHENTICATED_READ);
	}

	/**
	 * @return CM_KissTracking_Api
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new static();
		}
		return self::$_instance;
	}
}
