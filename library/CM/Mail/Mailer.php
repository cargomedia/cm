<?php

class CM_Mail_Mailer extends Swift_Mailer implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var array */
    private $_headers;

    /**
     * @param Swift_Transport $transport
     * @param array|null      $headers
     */
    public function __construct(Swift_Transport $transport, array $headers = null) {
        if (null === $headers) {
            $headers = [];
        }
        $this->_headers = $headers;

        CM_Mail_Message::register();
        parent::__construct($transport);
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        $failedRecipients = (array) $failedRecipients;
        $to = $message->getTo();
        if (empty($to)) {
            throw new CM_Exception_Invalid('No recipient specified');
        }

        $numSent = 0;
        try {
            $message = clone $message;
            foreach ($this->_getHeaders() as $key => $value) {
                $message->getHeaders()->addTextHeader($key, $value);
            }
            $numSent = parent::send($message, $failedRecipients);
            $this->getTransport()->stop();
            if (0 === $numSent || 0 !== count($failedRecipients)) {
                $this->_logSendError($message, $failedRecipients);
            }
        } catch (Exception $exception) {
            $this->_logSendError($message, $failedRecipients, $exception);
        }

        return $numSent;
    }

    public function createMessage($service = null) {
        $service = null === $service ? 'cm-message' : $service;
        return parent::createMessage($service);
    }

    /**
     * @return array
     */
    protected function _getHeaders() {
        return $this->_headers;
    }

    /**
     * @param Swift_Mime_Message $message
     * @param array|null         $failedRecipients
     * @param Exception|null     $exception
     */
    protected function _logSendError(Swift_Mime_Message $message, array $failedRecipients = null, Exception $exception = null) {
        $context = new CM_Log_Context();
        $context->setExtra([
            'message'          => [
                'subject' => $message->getSubject(),
                'from'    => $message->getFrom(),
                'to'      => $message->getTo(),
                'cc'      => $message->getCc(),
                'bcc'     => $message->getBcc(),
            ],
            'failedRecipients' => $failedRecipients,
        ]);
        if ($exception) {
            $context->setException($exception);
        }
        $this->getServiceManager()->getLogger()->error('Failed to send email', $context);
    }
}
