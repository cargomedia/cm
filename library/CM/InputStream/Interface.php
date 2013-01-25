<?php

interface CM_InputStream_Interface {

	/**
	 * @param string      $hint
	 * @param string|null $default
	 * @return string
	 */
	public function read($hint, $default = null);

	/**
	 * @param string        $hint
	 * @param string|null   $default
	 * @return boolean
	 */
	public function confirm($hint, $default = null);

}
