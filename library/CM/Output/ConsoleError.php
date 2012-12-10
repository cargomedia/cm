<?php

class CM_Output_ConsoleError extends CM_Output_Abstract {

	public function write($message) {
		$stream = fopen('php://stderr', 'w');
		fwrite($stream, $message);
		fclose($stream);
	}
}
