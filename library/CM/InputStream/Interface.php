<?php

interface CM_InputStream_Interface {

	/**
	 * @param string|null   $hint
	 * @param string|null   $default
	 * @return string
	 */
	public function read($hint = null, $default = null);

	/**
	 * @param string|null   $hint
	 * @param string|null   $default
	 * @return boolean
	 */
	public function confirm($hint = null, $default = null);

}
