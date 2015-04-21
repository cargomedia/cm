<?php

class CM_Paging_Log_Mail extends CM_Paging_Log_Abstract {

    public function add($msg, array $metaInfo = null) {
        throw new CM_Exception_NotImplemented;
    }

    /**
     * @param CM_Mail $mail
     * @param string  $msg
     */
    public function addMail(CM_Mail $mail, $msg) {
        $this->_add($msg, array(
            'sender'  => $mail->getSender(),
            'replyTo' => $mail->getReplyTo(),
            'to'      => $mail->getTo(),
            'cc'      => $mail->getCc(),
            'bcc'     => $mail->getBcc(),
        ));
    }
}
