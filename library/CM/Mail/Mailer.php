<?php

class CM_Mail_Mailer extends Swift_Mailer implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function __construct(Swift_Transport $transport) {
        CM_Mail_Message::register();
        parent::__construct($transport);
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        $failedRecipients = (array) $failedRecipients;
        $to = $message->getTo();
        if (empty($to)) {
            throw new CM_Exception_Invalid('No recipient specified');
        }
        $numSent = parent::send($message, $failedRecipients);
        if ($failedRecipients) {
            $context = new CM_Log_Context();
            $context->setExtra([
                'message'          => [
                    'subject' => $message->getSubject(),
                    'from'    => $message->getSender(),
                    'to'      => $message->getTo(),
                    'cc'      => $message->getCc(),
                    'bcc'     => $message->getBcc(),
                ],
                'failedRecipients' => $failedRecipients,
            ]);
            if (0 === $numSent) {
                $this->getServiceManager()->getLogger()->error('Failed to send email to all recipients', $context);
            } else {
                $this->getServiceManager()->getLogger()->warning('Failed to send email to some recipients', $context);
            }
        }
        return $numSent;
    }

    public function createMessage($service = null) {
        $service = null === $service ? 'cm-message' : $service;
        return parent::createMessage($service);
    }
}
