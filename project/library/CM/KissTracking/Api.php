<?php

class CM_KissTracking_Api extends CM_Class_Abstract {
	/**
	 * constant to keep track of the set name to be used for storing the data
	 */
	const setName = 'kisstracking';

	/**
	 * @var CM_KissTracking_Api
	 */
	private static $_instance = null;

	/**
	 * @var CM_Set
	 */
	private $_set;

	/**
	 * @return boolean
	 */
	public function enabled() {
		return (boolean) self::_getConfig()->enabled;
	}

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


	public function process() {
		$this->generateCsv();
		$this->_uploadCsv();
	}

	/**
	 * @return mixed[]
	 */
	public function _getEvents() {
		return $this->_getSet()->flush();
	}

	public function generateCsv() {
		$records = $this->_getEvents();
		$fileLocation = $this->_getFile();
		$csvString = '';
		$csvHeaderFinal = array();

		foreach ($records as $record) {
			foreach ($record as $key => $value) {
				$csvHeaderFinal[$key] = '';
			}
		}

		if (file_exists($fileLocation) && is_readable($fileLocation)) {
			$csvFp = fopen($fileLocation, 'r');
			//reading the first line from the csv
			$csvHeader = fgetcsv($csvFp);

			/**
			 * For merging later we need to transform this array from a scalar array to a assoc array with all the values null.
			 */
			$csvHeader = array_fill_keys($csvHeader, null);
			$initialCsvHeaderCount = count($csvHeader);
			$csvHeaderMerged = array_merge($csvHeader, $csvHeaderFinal);
			$numberHeaderItemsAdded = count($csvHeaderMerged) - $initialCsvHeaderCount;

			if ($numberHeaderItemsAdded) {
				//we need to add additional , to each row
				$stringToBeAdded = str_repeat(',', $numberHeaderItemsAdded);

				$csvString = '';
				while (($data = fgets($csvFp)) !== false) {
					$csvString .= preg_replace('/[.]*(\n)/', '$2' . $stringToBeAdded . "\n", $data);
 				}
			}
			fclose($csvFp);
			$csvHeaderFinal = $csvHeaderMerged;
		}

		$csvFpWrite = fopen($fileLocation, 'w');
		fputcsv($csvFpWrite, array_keys($csvHeaderFinal));
		fputs($csvFpWrite, $csvString);
		foreach ($records as $record) {
			$record = array_merge($csvHeaderFinal, $record);
			fputcsv($csvFpWrite, $record);
		}
		fclose($csvFpWrite);
	}

	/**
	 * @return string
	 */
	protected function _getFile() {
		return DIR_TMP.self::_getConfig()->csvFile;
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
	 * Uploads the csv file provided as in parameter
	 * @param Resource $csvFP
	 */
	protected function _uploadCsv() {
		require_once DIR_LIBRARY . 'amazon-s3-php-class'.DIRECTORY_SEPARATOR.'S3.php';
		$accessKey = self::_getConfig()->awsAccessKey;
		$secretKey = self::_getConfig()->awsSecretKey;
		$bucketName = self::_getConfig()->awsBucketName;
		$fileName = self::_getConfig()->s3FilePrefix. '-' . date('YmdHis');
		$s3 = new S3($accessKey, $secretKey);
		$s3->putObjectFile($this->_getFile(), $bucketName, $fileName, S3::ACL_PRIVATE);
	}

	/**
	 * @static
	 * @return CM_KissTracking_Api
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			$className = self::_getClassName();
			self::$_instance = new $className();
		}
		return self::$_instance;
	}

}
