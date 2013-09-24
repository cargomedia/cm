<?php

abstract class CM_ExceptionHandling_Formatter_Abstract {

	/**
	 * @param Exception $exception
	 * @return string
	 */
	public function formatException(Exception $exception) {
		return $this->format(new CM_ExceptionHandling_SerializedException($exception));
	}

	/**
	 * @param CM_ExceptionHandling_SerializedException $exception
	 * @return string
	 */
	public function format(CM_ExceptionHandling_SerializedException $exception) {
		$header = $this->getHeader($exception);
		if (null !== $exception->trace) {
			$trace = $this->getTrace($exception);
		} else {
			$trace = $exception->getTraceAsString();
		}
		return $header . $trace;
	}

	/**
	 * @param CM_ExceptionHandling_SerializedException $exception
	 * @return string
	 */
	abstract public function getHeader(CM_ExceptionHandling_SerializedException $exception);

	/**
	 * @param CM_ExceptionHandling_SerializedException $exception
	 * @return string
	 */
	abstract public function getTrace(CM_ExceptionHandling_SerializedException $exception);
}
