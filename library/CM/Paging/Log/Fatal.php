<?php

class CM_Paging_Log_Fatal extends CM_Paging_Log_Abstract {
	const TYPE = 5;

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$this->_add($msg, $this->_getMetafInfoFromRequest());
	}
}