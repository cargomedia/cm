<?php

interface CM_Output_Interface {

	/**
	 * @param string $message
	 */
	public function write($message);

	/**
	 * @param string $message
	 */
	public function writeln($message);

}
