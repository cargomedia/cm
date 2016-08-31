<?php

class CM_Mailer_Transport_Log implements Swift_Transport, CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var bool */
    private $_started;

    /** @var int */
    private $_logLevel;

    /**
     * @param int|null $logLevel
     */
    public function __construct($logLevel = null) {
        $this->_logLevel = null === $logLevel ? CM_Log_Logger::INFO : $logLevel;
        $this->_started = false;
    }

    public function isStarted() {
        return $this->_started;
    }

    public function start() {
        if (!$this->getServiceManager()) {
            throw new CM_Exception_Invalid('No service manager available');
        }
        if (!$this->getServiceManager()->hasLogger()) {
            throw new CM_Exception_Invalid('Logger service not available');
        }
        $this->_started = true;
    }

    public function stop() {
        $this->_started = false;
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        $msg = '* ' . $message->getSubject() . ' *' . PHP_EOL . PHP_EOL;
        $msg .= $message->getBody() . PHP_EOL;
        $logger = $this->getServiceManager()->getLogger();
        $context = new CM_Log_Context();
        $context->setExtra([
            'type'    => CM_Paging_Log_Mail::getTypeStatic(),
            'sender'  => $message->getSender(),
            'replyTo' => $message->getReplyTo(),
            'to'      => $message->getTo(),
            'cc'      => $message->getCc(),
            'bcc'     => $message->getBcc(),
        ]);
        $logger->addMessage($message, $this->_logLevel, $context);
    }

    public function registerPlugin(Swift_Events_EventListener $plugin) {
        throw new CM_Exception_NotImplemented();
    }
}
