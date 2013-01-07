<?php

class CM_Output_ConsoleOutput extends CM_Output_Abstract {

	public function write($message) {
		$stream = fopen('php://stdout', 'w');
		fwrite($stream, $message);
		fclose($stream);
	}
}
