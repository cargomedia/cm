<?php

class CM_Paging_Log_JsError extends CM_Paging_Log_Abstract {
	const TYPE = 6;

	/**
	 * @param string $msg
	 */
	public function add($msg) {
		$this->_add($msg, $this->_getMetafInfoFromRequest());
	}
}
