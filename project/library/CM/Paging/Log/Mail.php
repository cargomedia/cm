<?php

class CM_Paging_Log_Mail extends CM_Paging_Log_Abstract {
	const TYPE = 3;

	/**
	 * @param string $msg
	 * @param string $senderAddress
	 * @param string $recipientAddress
	 */
	public function add($msg, $senderAddress, $recipientAddress) {
		$this->_add($msg, array('senderAddress' => $senderAddress, 'recipientAddress' => $recipientAddress));
	}
}
