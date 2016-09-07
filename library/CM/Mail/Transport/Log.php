<?php

class CM_Mail_Transport_Log implements Swift_Transport {

    /** @var CM_Log_Logger */
    private $_logger;

    /** @var bool */
    private $_started;

    /** @var int */
    private $_logLevel;

    /**
     * @param int|null $logLevel
     */
    public function __construct(CM_Log_Logger $logger, $logLevel = null) {
        $this->_logger = $logger;
        $this->_logLevel = null === $logLevel ? CM_Log_Logger::INFO : $logLevel;
        $this->_started = false;
    }

    /**
     * @return CM_Log_Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return int
     */
    public function getLogLevel() {
        return $this->_logLevel;
    }

    public function isStarted() {
        return $this->_started;
    }

    public function start() {
        $this->_started = true;
    }

    public function stop() {
        $this->_started = false;
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null) {
        $failedRecipients = (array) $failedRecipients;

        $msg = '* ' . $message->getSubject() . ' *' . PHP_EOL . PHP_EOL;
        if ($message instanceof CM_Mail_Message) {
            $msg .= $message->getText() . PHP_EOL;
        } else {
            $msg .= $message->getBody() . PHP_EOL;
        }
        $logger = $this->getLogger();
        $context = new CM_Log_Context();
        $context->setExtra([
            'type'    => CM_Paging_Log_Mail::getTypeStatic(),
            'sender'  => $message->getSender(),
            'replyTo' => $message->getReplyTo(),
            'to'      => $message->getTo(),
            'cc'      => $message->getCc(),
            'bcc'     => $message->getBcc(),
        ]);
        $logger->addMessage($msg, $this->_logLevel, $context);

        return
            count($message->getTo()) +
            count($message->getCc()) +
            count($message->getBcc());
    }

    public function registerPlugin(Swift_Events_EventListener $plugin) {
        throw new CM_Exception_NotImplemented();
    }
}
