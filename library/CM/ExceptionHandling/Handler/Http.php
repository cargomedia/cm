<?php

class CM_ExceptionHandling_Handler_Http extends CM_ExceptionHandling_Handler_Abstract {

	protected function _printException(Exception $exception) {
		$output = new CM_OutputStream_Stream_Output();
		$formatter = new CM_ExceptionHandling_Formatter_Html();

		if (!headers_sent()) {
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-Type: text/html');
		}
		if (!CM_Bootloader::getInstance()->isDebug()) {
			$output->writeln('Internal server error');
		} else {
			$output->writeln($formatter->formatException($exception));
		}
	}
}
