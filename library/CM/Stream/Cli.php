<?php

class CM_Stream_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @synchronized
	 */
	public function runSynchronization() {
		CM_Stream_Stream::getInstance()->runSynchronization();
	}

	public static function getPackageName() {
		return 'stream';
	}

}