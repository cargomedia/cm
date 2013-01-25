<?php

class CM_InputStream_Null implements CM_InputStream_Interface {

	public function read() {
		throw new CM_Exception_Invalid('Cannot read input stream');
	}

}