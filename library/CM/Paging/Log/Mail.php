<?php

class CM_Paging_Log_Mail extends CM_Paging_Log_Abstract {

	/**
	 * @return int
	 */
	public function getType() {
		return self::TYPE_MAIL;
	}

	/**
	 * @param string $msg
	 * @param string $senderAddress
	 * @param string $recipientAddress
	 */
	public function add($msg, $senderAddress, $recipientAddress) {
		$this->_add($msg, array('senderAddress' => $senderAddress, 'recipientAddress' => $recipientAddress));
	}
}
