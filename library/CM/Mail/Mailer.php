<?php

class CM_Mail_Mailer extends Swift_Mailer {

    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        $failedRecipients = (array) $failedRecipients;
        $to = $message->getTo();
        if (empty($to)) {
            throw new CM_Exception_Invalid('No recipient specified');
        }
        $numSent = parent::send($message, $failedRecipients);
        if (0 === $numSent) {
            throw new CM_Exception_Invalid('Failed to send email', null, [
                'message'          => $message,
                'failedRecipients' => $failedRecipients,
            ]);
        }
        return $numSent;
    }

    public function createMessage($service = null) {
        CM_Mail_Message::register();
        $service = null === $service ? 'cm-message' : $service;
        return parent::createMessage($service);
    }
}
