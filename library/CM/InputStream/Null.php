<?php

class CM_InputStream_Null extends CM_InputStream_Abstract {

	protected function _read($hint = null) {
		throw new CM_Exception_Invalid('Cannot read input stream');
	}

}
