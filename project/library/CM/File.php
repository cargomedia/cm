<?php

class CM_File {
	private $_path;

	/**
	 * @param string|CM_File $file Path to file
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($file) {
		if ($file instanceof CM_File) {
			$file = $file->getPath();
		}
		$this->_path = (string) $file;
		if (!$this->getExists()) {
			throw new CM_Exception_Invalid('File path `' . $file . '` does not exist or is not a file.');
		}
	}

	/**
	 * @return string File path
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @return string File name
	 */
	public function getFileName() {
		return pathinfo($this->getPath(), PATHINFO_BASENAME);
	}

	/**
	 * @return int File size in bytes
	 * @throws CM_Exception
	 */
	public function getSize() {
		$size = filesize($this->getPath());
		if (false === $size) {
			throw new CM_Exception('Cannot detect filesize of `' . $this->getPath() . '`');
		}
		return $size;
	}

	/**
	 * @return string File mime type
	 * @throws CM_Exception
	 */
	public function getMimeType() {
		$info = new finfo(FILEINFO_MIME);
		$infoFile = $info->file($this->getPath());
		if (false === $infoFile) {
			throw new CM_Exception('Cannot detect FILEINFO_MIME of `' . $this->getPath() . '`');
		}
		$mime = explode(';', $infoFile);
		return $mime[0];
	}

	/**
	 * @return string File extension
	 */
	public function getExtension() {
		return strtolower(pathinfo($this->getFileName(), PATHINFO_EXTENSION));
	}

	/**
	 * @return string MD5-hash of file contents
	 * @throws CM_Exception
	 */
	public function getHash() {
		$md5 = md5_file($this->getPath());
		if (false === $md5) {
			throw new CM_Exception('Cannot detect md5-sum of `' . $this->getPath() . '`');
		}
		return $md5;
	}

	/**
	 * @return bool
	 */
	public function getExists() {
		return is_file($this->getPath());
	}

	/**
	 * @return string
	 * @throws CM_Exception
	 */
	public function read() {
		@$contents = file_get_contents($this->getPath());
		if ($contents === false) {
			throw new CM_Exception('Cannot read contents of `' . $this->getPath() . '`.');
		}
		return $contents;
	}

	/**
	 * @param $content
	 * @throws CM_Exception
	 */
	public function write($content) {
		if (false === file_put_contents($this->getPath(), $content)) {
			throw new CM_Exception('Could not write ' . strlen($content) . ' bytes to `' . $this->getPath() . '`');
		}
	}

	/**
	 * @param string $path New file path
	 * @throws CM_Exception
	 */
	public function copy($path) {
		$path = (string) $path;
		if (!@copy($this->getPath(), $path)) {
			throw new CM_Exception('Cannot copy `' . $this->getPath() . '` to `' . $path . '`.');
		}
	}

	/**
	 * @param string $path
	 * @throws CM_Exception
	 */
	public function move($path) {
		$path = (string) $path;
		if (!@rename($this->getPath(), $path)) {
			throw new CM_Exception('Cannot move `' . $this->getPath() . '` to `' . $path . '`.');
		}
		$this->_path = $path;
	}

	/**
	 * @throws CM_Exception
	 */
	public function delete() {
		if (!file_exists($this->getPath())) {
			return;
		}
		if (is_dir($this->getPath())) {
			CM_Util::rmDir($this->getPath());
		} else {
			if (!unlink($this->getPath())) {
				throw new CM_Exception_Invalid('Could not delete file `' . $this->getPath() . '`');
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->read();
	}

	/**
	 * @param string      $path
	 * @param string|null $content
	 * @return CM_File
	 * @throws CM_Exception
	 */
	public static function create($path, $content = null) {
		$content = (string) $content;
		if (false === file_put_contents($path, $content)) {
			throw new CM_Exception('Cannot write to `' . $path . '`.');
		}
		$file = new static($path);
		return $file;
	}
}
