<?php

class CM_File_Temp extends CM_File {
	/**
	 * @var string
	 */
	private $_uniqid;

	/**
	 * @var string
	 */
	private $_filename;

	/**
	 * @param string $uniqid
	 */
	public function __construct($uniqid) {
		$data = CM_Mysql::select(TBL_CM_TMP_USERFILE, '*', array('uniqid' => $uniqid))->fetchAssoc();
		if (!$data) {
			throw new CM_Exception_Nonexistent('Uniqid for file does not exists: `' . $uniqid . '`');
		}
		$this->_uniqid = $data['uniqid'];
		$this->_filename = $data['filename'];
	}

	/**
	 * @param string $filename
	 * @param string|null $content NOT USED
	 * @return CM_File_Temp
	 */
	public static function create($filename, $content = null) {
		$filename = (string) $filename;
		if (strlen($filename) > 100) {
			$filename = substr($filename, -1, 100);
		}
		$uniqid = md5(rand() . uniqid());
		CM_Mysql::insert(TBL_CM_TMP_USERFILE, array('uniqid' => $uniqid, 'filename' => $filename, 'createStamp' => time()));
		return new self($uniqid);
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return DIR_TMP_USERFILES . $this->getUniqid() . '.' . $this->getExtension();
	}

	/**
	 * @return string
	 */
	public function getUniqid() {
		return $this->_uniqid;
	}

	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->_filename;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return URL_TMP_USERFILES . $this->getUniqid() . '.' . $this->getExtension();
	}

	public function delete() {
		CM_Mysql::delete(TBL_CM_TMP_USERFILE, array('uniqid' => $this->getUniqid()));
		parent::delete();
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$query = CM_Mysql::exec('SELECT `uniqid` FROM `' . TBL_CM_TMP_USERFILE . '` WHERE `createStamp`< ?', time() - $age);
		$uniqueIds = $query->fetchCol();

		foreach ($uniqueIds as $uniqid) {
			$tmpFile = new CM_File_Temp($uniqid);
			$tmpFile->delete();
		}
	}
}
