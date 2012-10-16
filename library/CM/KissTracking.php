<?php

class CM_KissTracking extends CM_Class_Abstract {

	const setName = 'kisstracking';

	/** @var CM_KissTracking */
	private static $_instance;

	/** @var CM_Set */
	private $_set;

	/**
	 * @param string        $event
	 * @param CM_Model_User $user
	 * @param string|null   $alias
	 * @param array|null    $properties
	 */
	public function trackUser($event, CM_Model_User $user, $alias = null, array $properties = null) {
		$this->track($event, $user->getId(), $alias, $properties);
	}

	/**
	 * @param string        $event
	 * @param string        $identity
	 * @param string|null   $alias
	 * @param array|null    $properties
	 */
	public function track($event, $identity, $alias = null, array $properties = null) {
		if (!$this->_getEnabled()) {
			return;
		}
		$event = (string) $event;
		$identity = (string) $identity;
		$alias = (string) $alias;
		$properties = (array) $properties;
		$record = array('Identity' => $identity, 'Alias' => $alias, 'Timestamp' => time(), 'Event' => $event);
		foreach ($properties as $propName => &$propValue) {
			$record['Prop:' . $propName] = (string) $propValue;
		}

		$this->_getSet()->add($record);
	}

	public function exportEvents() {
		if (!$this->_getEnabled()) {
			return;
		}
		$file = $this->generateCsv();
		$lastUploadAt = CM_Option::getInstance()->get('kissTracking.lastUpload');
		if (time() - $lastUploadAt > 7200) {
			$this->_uploadCsv($file);
			$file->delete();
			CM_Option::getInstance()->set('kissTracking.lastUpload', time());
		}
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

		if (!CM_File::exists($filename)) {
			/** @var $file CM_File_Csv */
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
	protected function _getFileName() {
		return DIR_DATA . 'kiss-tracking.csv';
	}

	/**
	 * @param CM_File_Csv $file
	 */
	private function _uploadCsv(CM_File_Csv $file) {
		$bucketName = self::_getConfig()->awsBucketName;
		$targetFilename = self::_getConfig()->awsFilePrefix . '.' . date('YmdHis') . '.csv';

		$amazonS3 = new CM_Amazon_S3();
		$amazonS3->upload($file, $bucketName, $targetFilename, array('6acb81d7742ac437833f51ecb2a40c74cd831ce26909e5f72354fa6af42cfb1f' => 'full-control'));
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
	 * @return boolean
	 */
	private function _getEnabled() {
		return (bool) self::_getConfig()->enabled;
	}

	/**
	 * @return CM_KissTracking
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new static();
		}
		return self::$_instance;
	}
}
