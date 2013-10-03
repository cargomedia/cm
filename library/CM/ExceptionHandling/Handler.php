<?php

class CM_ExceptionHandling_Handler {

	/**
	 * @param int    $code
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 * @return bool
	 * @throws ErrorException
	 */
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
	 * @param Exception                                    $exception
	 * @param CM_OutputStream_Interface|null               $output
	 * @param CM_ExceptionHandling_Formatter_Abstract|null $formatter
	 */
	public function handleException(Exception $exception, CM_OutputStream_Interface $output = null, CM_ExceptionHandling_Formatter_Abstract $formatter = null) {
		$this->_logException($exception);
		$this->_printException($exception, $output, $formatter);
	}

	/**
	 * @param Exception $exception
	 */
	protected function _logException(Exception $exception) {
		$formatter = new CM_ExceptionHandling_Formatter_Plain();
		try {
			if ($exception instanceof CM_Exception) {
				$log = $exception->getLog();
			} else {
				$log = new CM_Paging_Log_Error();
			}
			$log->add($formatter->formatException($exception));
		} catch (Exception $loggerException) {
			$logEntry = '[' . date('d.m.Y - H:i:s', time()) . ']' . PHP_EOL;
			$logEntry .= '### Cannot log error: ' . PHP_EOL;
			$logEntry .= $formatter->formatException($loggerException);
			$logEntry .= '### Original Exception: ' . PHP_EOL;
			$logEntry .= $formatter->formatException($exception) . PHP_EOL;
			file_put_contents(DIR_DATA_LOG . 'error.log', $logEntry, FILE_APPEND);
		}
	}

	/**
	 * @param Exception                                    $exception
	 * @param CM_OutputStream_Abstract|null                $output
	 * @param CM_ExceptionHandling_Formatter_Abstract|null $formatter
	 */
	protected function _printException(Exception $exception, CM_OutputStream_Abstract $output = null, CM_ExceptionHandling_Formatter_Abstract $formatter = null) {
		if (null === $output) {
			$output = new CM_OutputStream_Stream_Output();
		}

		$isHttpRequest = !CM_Bootloader::getInstance()->isEnvironment('cli') && !CM_Bootloader::getInstance()->isEnvironment('test');
		if ($isHttpRequest) {
			if (!headers_sent()) {
				header('HTTP/1.1 500 Internal Server Error');
				header('Content-Type: text/html');
			}
			if (null === $formatter) {
				$formatter = new CM_ExceptionHandling_Formatter_Html();
			}
		}
		if (null === $formatter) {
			$formatter = new CM_ExceptionHandling_Formatter_Plain();
		}

		if ($isHttpRequest && !IS_DEBUG) {
			$output->writeln('Internal server error');
		} else {
			$output->writeln($formatter->formatException($exception));
		}

		if (!$exception instanceof CM_Exception || $exception->getSeverity() >= CM_Exception::ERROR) {
			CMService_Newrelic::getInstance()->setNoticeError($exception);
		}
	}
}
