<?php

class CM_File_Temp extends CM_File {

	private static $_errIndex = array(
		UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
		UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
	);

	private $_uniqid;

	private $_type;

	private $_filename;

	/**
	 * @param string $uniqid
	 */
	public function __construct($uniqid) {
		$file = CM_Mysql::select(TBL_CM_TMP_USERFILE, '*', array('uniqid' => $uniqid))->fetchAssoc();
		if (!$file) {
			throw new CM_Exception_Nonexistent('Uniqid for file does not exists: `' . $uniqid . '`');
		}
		
		$this->_uniqid = $file['uniqid'];
		$this->_filename = $file['filename'];
	}

	/**
	 * @param array $file Params: name, extension, size, (error)
	 * @return CM_File_Temp
	 * @throws CM_Exception
	 */
	public static function create(array $file) {
		if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
			throw new CM_Exception(self::$_errIndex[$file['error']], $file['error']);
		}

		$filename = $file['name'];

		// Limit filename to 100 chars
		if (strlen($filename) > 100) {
			$info = pathinfo($filename);

			if (isset($info['extension'])) {
				$filename = substr($fileame, 0, 90) . '.' . $info['extension'];
			} else {
				$filename = substr($filename, 0, 100);
			}
		}

		$uniqid = md5(uniqid(rand(), true));

		CM_Mysql::insert(TBL_CM_TMP_USERFILE,
			array(
				'uniqid' => $uniqid,
				'filename' => $filename,
				'size' => $file['size'],
				'uploadstamp' => time(),
			)
		);

		return new self($uniqid);
	}

	/**
	 * Get a full path to a temporary file.
	 *
	 * @return string
	 */
	public function getPath() {
		return DIR_TMP_USERFILES . $this->getUniqid() . '.' . $this->getExtension();
	}

	/**
	 * Get a file unique id.
	 *
	 * @return string
	 */
	public function getUniqid() {
		return $this->_uniqid;
	}

	/**
	 * Get a client filename.
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->_filename;
	}

	/**
	 * Get a file URL.
	 *
	 * @return string
	 */
	public function getURL() {
		return URL_TMP_USERFILES . $this->getUniqid() . '.' . $this->getExtension();
	}

	public function delete() {
		CM_Mysql::delete(TBL_CM_TMP_USERFILE, array('uniqid' => $this->getUniqid()));
		parent::delete();
	}
	
	/**
	 * Write the given content to disk
	 * 
	 * @param string $content
	 * @throws CM_Exception
	 */
	public function writeContent($content) {
		if (false === file_put_contents($this->getPath(), $content)) {
			throw new CM_Exception('Could not write ' . strlen($content) . ' bytes to path `' . $this->getPath() . '`');
		}
	}
	
	/**
	* @param int $age
	*/
	public static function deleteOlder($age) {
		$query = CM_Mysql::exec('SELECT `uniqid` FROM `' . TBL_CM_TMP_USERFILE . '` WHERE `uploadstamp`< ?', time() - $age);
		$uniqueIds = $query->fetchCol();
	
		foreach ($uniqueIds as $uniqid) {
			$tmpFile = new CM_File_Temp($uniqid);
			$tmpFile->delete();
		}
	}
}
