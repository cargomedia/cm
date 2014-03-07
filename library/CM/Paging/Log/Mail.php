<?php

class CM_Paging_Log_Mail extends CM_Paging_Log_Abstract {

    /**
     * @param CM_Mail $mail
     * @param string  $msg
     */
    public function add(CM_Mail $mail, $msg) {
        $this->_add($msg, array('sender' => $mail->getSender(), 'replyTo' => $mail->getReplyTo(), 'to' => $mail->getTo(), 'cc' => $mail->getCc(),
                                'bcc'    => $mail->getBcc()));
    }
}
