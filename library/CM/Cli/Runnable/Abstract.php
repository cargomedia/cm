<?php

abstract class CM_Cli_Runnable_Abstract {

	/**
	 * @throws CM_Exception_NotImplemented
	 * @return string
	 */
	public static function getPackageName() {
		throw new CM_Exception_NotImplemented();
	}

}