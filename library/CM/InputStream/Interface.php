<?php

interface CM_InputStream_Interface {

	/**
	 * @param string $hint
	 * @return string
	 */
	public function read($hint);

	/**
	 * @param string $hint
	 * @return boolean
	 */
	public function confirm($hint);

}
