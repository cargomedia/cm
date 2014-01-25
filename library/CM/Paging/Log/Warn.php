<?php

class CM_Paging_Log_Warn extends CM_Paging_Log_Abstract {

	const TYPE = 72;

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$this->_add($msg, $this->_getMetafInfoFromRequest());
	}
}
