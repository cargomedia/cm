<?php

class CM_Paging_Log_Mail extends CM_Paging_Log_Abstract {
	const TYPE = 3;

	/**
	 * @param CM_Mail $mail
	 * @param $msg
	 */
	public function add(CM_Mail $mail, $msg) {
		$this->_add($msg, array('sender' => $mail->getSender(), 'replyTo' => $mail->getReplyTo(), 'to' => $mail->getTo(), 'cc' => $mail->getCc(), 'bcc' => $mail->getBcc()));
	}
}
