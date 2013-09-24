<?php

class CM_ExceptionHandling_SerializedException {

	/** @var string */
	public $class;

	/** @var string */
	public $message;

	/** @var int */
	public $line;

	/** @var string */
	public $file;

	/** @var array|null */
	public $trace;

	/** @var array */
	public $traceString;

	/**
	 * @param Exception $exception
	 */
	public function __construct(Exception $exception) {
		$this->_extract($exception);
	}

	/**
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return int
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @return array|null
	 */
	public function getTrace() {
		return $this->trace;
	}

	/**
	 * @return array
	 */
	public function getTraceAsString() {
		return $this->traceString;
	}

	/**
	 * @param Exception $exception
	 */
	private function _extract(Exception $exception) {
		$this->class = get_class($exception);
		$this->message = $exception->getMessage();
		$this->line = $exception->getLine();
		$this->file = $exception->getFile();

		try {
			$trace = array();
			$trace[] = array(
				'code' => '{main}',
				'line'     => 0,
				'file'     => $_SERVER['SCRIPT_FILENAME'],
			);
			foreach (array_reverse($exception->getTrace()) as $row) {
				$trace[] = $this->_extractTraceRow($row);
			}
			$this->trace = $trace;
		} catch (Exception $e) {
			$this->trace = null;
			$this->traceString = $e->getTraceAsString();
		}
	}

	/**
	 * @param array $row
	 * @return array
	 */
	private function _extractTraceRow($row) {
		$traceEntry = array();
		$code = '';
		if (array_key_exists('function', $row)) {
			if (array_key_exists('class', $row)) {
				$code .= $row['class'] . '->';
			}
			$code .= $row['function'];
			if (array_key_exists('args', $row)) {
				$arguments = array();
				foreach ($row['args'] as $argument) {
					$arguments[] = CM_Util::varDump($argument);
				}
				$code .= '(' . implode(', ', $arguments) . ')';
			}
			$traceEntry['code'] = $code;
		}
		if (array_key_exists('file', $row)) {
			$file = $row['file'];
			$line = $row['line'];
		} else {
			$file = null;
			$line = null;
			$code = '[internal function]';
		}
		return array('code' => $code, 'file' => $file, 'line' => $line);
	}
}
