<?php

class CM_ExceptionHandler {

	public function handleErrorRaw($code, $message, $file, $line) {
		$errorCodes = array(
			E_ERROR             => 'E_ERROR',
			E_WARNING           => 'E_WARNING',
			E_PARSE             => 'E_PARSE',
			E_NOTICE            => 'E_NOTICE',
			E_CORE_ERROR        => 'E_CORE_ERROR',
			E_CORE_WARNING      => 'E_CORE_WARNING',
			E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
			E_USER_ERROR        => 'E_USER_ERROR',
			E_USER_WARNING      => 'E_USER_WARNING',
			E_USER_NOTICE       => 'E_USER_NOTICE',
			E_STRICT            => 'E_STRICT',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED        => 'E_DEPRECATED',
			E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			E_ALL               => 'E_ALL',
		);
		$message = $errorCodes[$code] . ': ' . $message;
		if (!(error_reporting() & $code)) {
			// This error code is not included in error_reporting
			$atSign = (0 === error_reporting()); // http://php.net/manual/en/function.set-error-handler.php
			if ($atSign) {
				return true;
			}
		}
		throw new ErrorException($message, 0, $code, $file, $line);
	}

	/**
	 * @param Exception                      $exception
	 * @param CM_OutputStream_Interface|null $output
	 */
	public function handleException(Exception $exception, CM_OutputStream_Interface $output = null) {
		$this->_logException($exception);
		$this->_printException($exception, $output);
	}

	/**
	 * @param Exception $exception
	 */
	private function _logException(Exception $exception) {
		try {
			if ($exception instanceof CM_Exception) {
				$log = $exception->getLog();
			} else {
				$log = new CM_Paging_Log_Error();
			}
			$log->add($this->_formatException($exception));
		} catch (Exception $loggerException) {
			$logEntry = '[' . date('d.m.Y - H:i:s', time()) . ']' . PHP_EOL;
			$logEntry .= '### Cannot log error: ' . PHP_EOL;
			$logEntry .= $this->_formatException($loggerException);
			$logEntry .= '### Original Exception: ' . PHP_EOL;
			$logEntry .= $this->_formatException($exception) . PHP_EOL;
			file_put_contents(DIR_DATA_LOG . 'error.log', $logEntry, FILE_APPEND);
		}
	}

	/**
	 * @param Exception                $exception
	 * @param CM_OutputStream_Abstract $output
	 */
	private function _printException(Exception $exception, CM_OutputStream_Abstract $output = null) {
		if (null === $output) {
			$output = new CM_OutputStream_Stream_Output();
		}
		if (!CM_Bootloader::getInstance()->isEnvironment('cli') && !CM_Bootloader::getInstance()->isEnvironment('test')) {
			header('HTTP/1.1 500 Internal Server Error');
		}

		$outputVerbose = IS_DEBUG || CM_Bootloader::getInstance()->isEnvironment('cli') || CM_Bootloader::getInstance()->isEnvironment('test');
		if ($outputVerbose) {
			$output->writeln($this->_formatException($exception));
		} else {
			$output->writeln('Internal server error');
		}

		if (!$exception instanceof CM_Exception || $exception->getSeverity() >= CM_Exception::ERROR) {
			CMService_Newrelic::getInstance()->setNoticeError($exception);
		}
	}

	/**
	 * @param Exception $exception
	 * @return string
	 */
	private function _formatException(Exception $exception) {
		$exceptionHeader = function (Exception $exception) {
			return get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() .
			PHP_EOL . PHP_EOL;
		};
		try {
			$exceptionMessage = '';
			$trace = array_reverse($exception->getTrace());
			array_unshift($trace, array(
				'function' => '{main}',
				'line'     => 0,
				'file'     => $_SERVER['SCRIPT_FILENAME'],
			));

			$indent = strlen(count($trace)) + 4;
			foreach ($trace as $number => $entry) {
				$exceptionMessage .= str_pad($number, $indent, ' ', STR_PAD_LEFT) . '. ';
				if (array_key_exists('function', $entry)) {
					if (array_key_exists('class', $entry)) {
						$exceptionMessage .= $entry['class'] . '->';
					}
					$exceptionMessage .= $entry['function'];
					if (array_key_exists('args', $entry)) {
						$arguments = array();
						foreach ($entry['args'] as $argument) {
							$arguments[] = CM_Util::varDump($argument);
						}
						$exceptionMessage .= '(' . implode(', ', $arguments) . ')';
					}
				}
				if (array_key_exists('file', $entry)) {
					$exceptionMessage .= ' ' . $entry['file'] . ':' . $entry['line'];
				} else {
					$exceptionMessage .= ' [internal function]';
				}
				$exceptionMessage .= PHP_EOL;
			}
			$output = $exceptionHeader($exception) . $exceptionMessage;
		} catch (Exception $e) {
			$output = $exceptionHeader($e) . $e->getTraceAsString();
			$output .= PHP_EOL . PHP_EOL;
			$output .= $exceptionHeader($exception) . $exception->getTraceAsString();
		}
		return $output;
	}
}
