<?php

abstract class CM_OutputStream_Abstract implements CM_OutputStream_Interface {

	/**
	 * @param string $message
	 */
	public function writeln($message) {
		$this->write($message . PHP_EOL);
	}

}
