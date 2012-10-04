<?php

class CM_File_Csv extends CM_File {

	/**
	 * @return string[]
	 */
	public function getHeader() {
		return $this->_convertStringToRow($this->_readFirstLine());
	}

	/**
	 * @param string[] $row
	 */
	public function prependRow(array $row) {
		$content = $this->_convertRowToString($row) . $this->read();
		$this->write($content);
	}

	/**
	 * @param string[] $row
	 */
	public function appendRow(array $row) {
		$line = $this->_convertRowToString($row);
		$this->append($line);
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
			$content = preg_replace('/\n/', $missingCommas . '$0', $this->read());
			$content = preg_replace('/^.*\n/', $this->_convertRowToString($header), $content, 1);
			$this->write($content);
		}
		return $header;
	}

	/**
	 * @param string[] $row
	 * @return string
	 */
	private function _convertRowToString(array $row) {
		$resource = fopen('php://memory', 'w+');
		fputcsv($resource, $row);
		rewind($resource);
		$line = fgets($resource);
		fclose($resource);
		return $line;
	}

	/**
	 * @param string $line
	 * @return string[]
	 */
	private function _convertStringToRow($line) {
		$resource = fopen('php://memory', 'w+');
		fputs($resource, $line);
		rewind($resource);
		$row = fgetcsv($resource);
		fclose($resource);
		return $row;
	}
}
