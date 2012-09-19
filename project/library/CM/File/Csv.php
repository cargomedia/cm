<?php

class CM_File_Csv extends CM_File {

	/**
	 * @return array
	 */
	public function getHeader() {
		$resource = fopen($this->getPath(), 'r');
		$header = fgetcsv($resource);
		fclose($resource);
		return $header;
	}

	/**
	 * @param array $row
	 */
	public function prependRow(array $row) {
		$content = $this->read();
		$this->truncate();
		$this->appendRow($row);
		$this->append($content);
	}

	/**
	 * @param array         $row
	 */
	public function appendRow(array $row) {
		$resource = fopen($this->getPath(), 'a');
		fputcsv($resource, $row);
		fclose($resource);
	}

	/**
	 * @param array $row
	 */
	public function replaceHeader(array $row) {
		$this->_removeHeader();
		$this->prependRow($row);
	}

	/**
	 * @param string[] $header
	 * @return string[]
	 */
	public function mergeHeader(array $header) {
		$headerOld = $this->getHeader();
		if (!$headerOld) {
			$headerOld = array();
		}
		$header = array_values($header);

		$header = array_merge($headerOld, $header);
		$header = array_unique($header);

		$headerNew = array_diff($header, $headerOld);
		if ($headerNew) {
			$missingCommas = str_repeat(',', count($headerNew));
			$content = preg_replace('/([.]*)(\n)/', '$1' . $missingCommas . '$2', $this->read());
			$this->write($content);
			$this->replaceHeader($header);
		}
		return $header;
	}

	private function _removeHeader() {
		$resource = fopen($this->getPath(), 'r');
		$header = fgets($resource);
		$this->write(substr($this->read(), strlen($header)));
		fclose($resource);
	}
}
