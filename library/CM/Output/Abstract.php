<?php

abstract class CM_Output_Abstract implements CM_Output_Interface {

	/**
	 * @param string $message
	 */
	public function writeln($message) {
		$this->write($message . PHP_EOL);
	}

}
