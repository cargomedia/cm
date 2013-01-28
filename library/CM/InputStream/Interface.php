<?php

interface CM_InputStream_Interface {

	/**
	 * @param string|null $hint
	 * @return string
	 */
	public function read($hint = null);

}
