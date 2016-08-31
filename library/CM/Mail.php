<?php

class CM_Mail extends CM_View_Abstract implements CM_Typed {

    /** @var CM_Model_User|null */
    private $_recipient;

    /** @var CM_Site_Abstract */
    private $_site;

    /** @var CM_Mailer_Client */
    private $_mailer;

    /** @var  Swift_Message */
    private $_message;

    /** @var boolean */
    private $_verificationRequired;

    /** @var boolean */
    private $_renderLayout;

    /** @var array */
    protected $_tplParams;

    /**
     * @param CM_Model_User|string|null $recipient
     * @param array|null                $tplParams
     * @param CM_Site_Abstract|null     $site
     * @param CM_Mailer_Client|null     $mailer
     * @throws CM_Exception_Invalid
     */
    public function __construct($recipient = null, array $tplParams = null, CM_Site_Abstract $site = null, CM_Mailer_Client $mailer = null) {
        $this->_renderLayout = false;
        $this->_verificationRequired = true;
        $this->_tplParams = [];

        if (null !== $mailer) {
            $this->_mailer = $mailer;
        }
        if ($this->hasTemplate()) {
            $this->setRenderLayout(true);
        }
        if ($tplParams) {
            foreach ($tplParams as $key => $value) {
                $this->setTplParam($key, $value);
            }
        }
        if (!is_null($recipient)) {
            if (is_string($recipient)) {
                $this->addTo($recipient);
            } elseif ($recipient instanceof CM_Model_User && $recipient->getEmail()) {
                $this->_recipient = $recipient;
                $this->addTo($this->_recipient->getEmail());
                $this->setTplParam('recipient', $recipient);
            } else {
                throw new CM_Exception_Invalid('Invalid Recipient defined.');
            }
        }

        if (!$site && $this->_recipient) {
            $site = $this->_recipient->getSite();
        }
        if (!$site) {
            $site = CM_Site_Abstract::factory();
        }
        $this->_site = $site;

        $this->setTplParam('siteName', $this->_site->getName());
        $this->setSender($this->_site->getEmailAddress(), $this->_site->getName());
    }

    /**
     * @param boolean|null $delayed
     * @throws CM_Exception_Invalid
     */
    public function send($delayed = null) {
        $delayed = (boolean) $delayed;
        if ($this->getVerificationRequired() && $this->hasRecipient() && !$this->getRecipient()->getEmailVerified()) {
            return;
        }

        list($subject, $html, $text) = $this->render();

        if ($delayed) {
            $this->_queue($subject, $text, $html);
        } else {
            $this->_send($subject, $text, $html);
        }
    }

    public function sendDelayed() {
        $this->send(true);
    }

    /**
     * @return array array($subject, $html, $text)
     */
    public function render() {
        $render = $this->getRender();
        $renderAdapter = new CM_RenderAdapter_Mail($render, $this);
        return $renderAdapter->fetch();
    }

    /**
     * @return CM_Model_User|null
     */
    public function getRecipient() {
        return $this->_recipient;
    }

    /**
     * @return boolean
     */
    public function hasRecipient() {
        return (boolean) $this->_recipient;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @return CM_Mailer_Message
     */
    public function getMessage() {
        if (!$this->_message) {
            $this->_message = $this->getMailer()->createMessage();
            $this->_message->setCharset('utf-8');
        }
        return $this->_message;
    }

    /**
     * @return CM_Mailer_Client
     */
    public function getMailer() {
        if (!$this->_mailer) {
            $this->_mailer = CM_Service_Manager::getInstance()->getMailer();
        }
        return $this->_mailer;
    }

    /**
     * @return CM_Frontend_Render
     */
    public function getRender() {
        $environment = $this->_recipient ? $this->_recipient->getEnvironment() : new CM_Frontend_Environment();
        $environment->setSite($this->_site);
        return new CM_Frontend_Render($environment);
    }

    /**
     * @return boolean
     */
    public function getRenderLayout() {
        return $this->_renderLayout;
    }

    /**
     * @param boolean|null $state
     */
    public function setRenderLayout($state = null) {
        $this->_renderLayout = (boolean) $state;
    }

    /**
     * @return array
     */
    public function getTplParams() {
        return $this->_tplParams;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return CM_Mail
     */
    public function setTplParam($key, $value) {
        $this->_tplParams[$key] = $value;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getVerificationRequired() {
        return (boolean) $this->_verificationRequired;
    }

    /**
     * @param boolean|null $state
     */
    public function setVerificationRequired($state = null) {
        $this->_verificationRequired = (boolean) $state;
    }

    /**
     * @return boolean
     */
    public function hasTemplate() {
        return is_subclass_of($this, 'CM_Mail');
    }

    /**
     * @return string
     */
    public function getSubject() {
        return $this->getMessage()->getSubject();
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->getMessage()->setSubject($subject);
    }

    /**
     * @return string|null
     */
    public function getHtml() {
        return $this->getMessage()->getHtml();
    }

    /**
     * @return string|null
     */
    public function getText() {
        return $this->getMessage()->getText();
    }

    /**
     * @return string
     */
    public function getSender() {
        return $this->getMessage()->getSender();
    }

    /**
     * @param string      $address
     * @param string|null $name
     */
    public function setSender($address, $name = null) {
        $address = (string) $address;
        $name = is_null($name) ? $name : (string) $name;
        $this->getMessage()->setSender($address, $name);
    }

    /**
     * @return array
     */
    public function getTo() {
        return $this->getMessage()->getTo();
    }

    /**
     * @param string      $address
     * @param string|null $name
     */
    public function addTo($address, $name = null) {
        $address = (string) $address;
        $name = is_null($name) ? $name : (string) $name;
        $this->getMessage()->addTo($address, $name);
    }

    /**
     * @return array
     */
    public function getCc() {
        return $this->getMessage()->getCc();
    }

    /**
     * @param string      $address
     * @param string|null $name
     */
    public function addCc($address, $name = null) {
        $address = (string) $address;
        $name = is_null($name) ? $name : (string) $name;
        $this->getMessage()->addCc($address, $name);
    }

    /**
     * @return array
     */
    public function getBcc() {
        return $this->getMessage()->getBcc();
    }

    /**
     * @param string      $address
     * @param string|null $name
     */
    public function addBcc($address, $name = null) {
        $address = (string) $address;
        $name = is_null($name) ? $name : (string) $name;
        $this->getMessage()->addBcc($address, $name);
    }

    /**
     * @return array
     */
    public function getReplyTo() {
        return $this->getMessage()->getReplyTo();
    }

    /**
     * @param string      $address
     * @param string|null $name
     */
    public function addReplyTo($address, $name = null) {
        $address = (string) $address;
        $name = is_null($name) ? $name : (string) $name;
        $this->getMessage()->addReplyTo($address, $name);
    }

    /**
     * @return array
     */
    public function getCustomHeaders() {
        return $this->getMessage()->getCustomHeaders();
    }

    /**
     * @param string $label
     * @param string $value
     */
    public function addCustomHeader($label, $value) {
        $label = (string) $label;
        $value = (string) $value;
        $this->getMessage()->getHeaders()->addTextHeader($label, $value);
    }

    /**
     * @return int
     */
    public static function getQueueSize() {
        return CM_Db_Db::count('cm_mail');
    }

    /**
     * @param int $limit
     */
    public static function processQueue($limit) {
        $limit = (int) $limit;
        $result = CM_Db_Db::execRead('SELECT * FROM `cm_mail` ORDER BY `createStamp` LIMIT ' . $limit);
        $readEmails = function ($data, $key) {
            $val = unserialize($data[$key]);
            return is_array($val) ? $val : [];
        };
        while ($row = $result->fetch()) {
            $mail = new CM_Mail();
            foreach ($readEmails($row, 'to') as $address => $name) {
                $mail->addTo($address, $name);
            }
            foreach ($readEmails($row, 'replyTo') as $address => $name) {
                $mail->addReplyTo($address, $name);
            }
            foreach ($readEmails($row, 'cc') as $address => $name) {
                $mail->addCc($address, $name);
            }
            foreach ($readEmails($row, 'bcc') as $address => $name) {
                $mail->addBcc($address, $name);
            }
            if ($headerList = unserialize($row['customHeaders'])) {
                foreach ($headerList as $label => $valueList) {
                    foreach ($valueList as $value) {
                        $mail->addCustomHeader($label, $value);
                    }
                }
            }
            $sender = unserialize($row['sender']);
            $mail->setSender(key($sender), $sender[key($sender)]);
            $mail->_send($row['subject'], $row['text'], $row['html']);
            CM_Db_Db::delete('cm_mail', ['id' => $row['id']]);
        }
    }

    /**
     * @throws CM_Exception_Invalid
     */
    protected function _send($subject, $text, $html = null) {
        $this->setSubject($subject);
        $this->getMessage()->setBodyWithAlternative($text, $html);
        $this->getMailer()->send($this->getMessage());

        if ($recipient = $this->getRecipient()) {
            $action = new CM_Action_Email(CM_Action_Abstract::SEND, $recipient, $this->getType());
            $action->prepare($recipient);
            $action->notify($recipient);
        }
    }

    private function _queue($subject, $text, $html) {
        CM_Db_Db::insert('cm_mail', [
            'subject'       => $subject,
            'text'          => $text,
            'html'          => $html,
            'createStamp'   => time(),
            'sender'        => serialize($this->getSender()),
            'replyTo'       => serialize($this->getReplyTo()),
            'to'            => serialize($this->getTo()),
            'cc'            => serialize($this->getCc()),
            'bcc'           => serialize($this->getBcc()),
            'customHeaders' => serialize($this->getCustomHeaders()),
        ]);
    }
}
