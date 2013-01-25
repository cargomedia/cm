<?php

interface CM_InputStream_Interface {

	/**
	 * @param string $hint
	 * @return string
	 */
	public function read($hint);

}
