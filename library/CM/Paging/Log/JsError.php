<?php

class CM_Paging_Log_JsError extends CM_Paging_Log_Abstract {

	const TYPE = 6;

	/**
	 * @param string     $msg
	 * @param array|null $metaInfo
	 */
	public function add($msg, array $metaInfo = null) {
		if (null === $metaInfo) {
			$metaInfo = array();
		}
		$metaInfo = array_merge($metaInfo, $this->_getMetafInfoFromRequest());
		$this->_add($msg, $metaInfo);
	}
}
