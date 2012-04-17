<?php

class CM_File_UserContent extends CM_File {
	const BUCKETS_COUNT = 10000;

	/**
	 * @var string
	 */
	private $_namespace;

	/**
	 * @var string
	 */
	private $_filename;

	/**
	 * @var int
	 */
	private $_sequence;

	/**
	 * @param string $namespace
	 * @param string $filename
	 * @param int	$sequence
	 */
	public function __construct($namespace, $filename, $sequence) {
		$this->_namespace = (string) $namespace;
		$this->_filename = (string) $filename;
		$this->_sequence = (int) $sequence;
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
	public function getPathRelative() {
		$bucket = $this->_sequence % self::BUCKETS_COUNT;
		return $this->_namespace . DIRECTORY_SEPARATOR . $bucket . DIRECTORY_SEPARATOR . $this->getFileName();
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return DIR_USERFILES . $this->getPathRelative();
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return URL_USERFILES . $this->getPathRelative();
	}

	public function mkDir() {
		$bucket = $this->_sequence % self::BUCKETS_COUNT;
		CM_Util::mkDir(DIR_USERFILES . $this->_namespace . DIRECTORY_SEPARATOR . $bucket);
	}

	/**
	 * @param string $namespace
	 */
	public static function mkDirAll($namespace) {
		for ($bucket = 0; $bucket < self::BUCKETS_COUNT; $bucket++) {
			CM_Util::mkDir(DIR_USERFILES . $namespace . DIRECTORY_SEPARATOR . $bucket);
		}
	}
}
