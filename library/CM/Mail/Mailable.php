<?php

class CM_Mail_Mailable extends CM_View_Abstract implements CM_Typed {

    /** @var CM_Model_User|null */
    private $_recipient;

    /** @var CM_Site_Abstract */
    private $_site;

    /** @var CM_Mail_Mailer */
    private $_mailer;

    /** @var  CM_Mail_Message */
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
     * @param CM_Mail_Mailer|null       $mailer
     * @throws CM_Exception_Invalid
     */
    public function __construct($recipient = null, array $tplParams = null, CM_Site_Abstract $site = null, CM_Mail_Mailer $mailer = null) {
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
            $site = (new CM_Site_SiteFactory())->getDefaultSite();
        }
        $this->_site = $site;

        $this->setTplParam('siteName', $this->_site->getName());
        $this->setSender($this->_site->getEmailAddress(), $this->_site->getName());
    }

    public function send() {
        $recipient = $this->getRecipient();
        if ($this->getVerificationRequired() && $recipient && !$recipient->getEmailVerified()) {
            return;
        }

        list($subject, $html, $text) = $this->render();
        $message = $this->getMessage();
        $message->setSubject($subject);
        $message->setBodyWithAlternative($text, $html);

        
        $params = ['message' => $message];
        if ($recipient) {
            $params['recipient'] = $recipient;
            $params['mailType'] = $this->getType();
        }
        $job = new CM_Mail_SendJob(CM_Params::factory($params));
        CM_Service_Manager::getInstance()->getJobQueue()->queue($job);
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
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
    }

    /**
     * @return CM_Mail_Message
     */
    public function getMessage() {
        if (!$this->_message) {
            $this->_message = $this->getMailer()->createMessage();
            $this->_message->setCharset('utf-8');
        }
        return $this->_message;
    }

    /**
     * @return CM_Mail_Mailer
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
        $state = null !== $state ? (boolean) $state : true;
        $this->_renderLayout = $state;
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
     * @return CM_Mail_Mailable
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
        $state = null !== $state ? (boolean) $state : true;
        $this->_verificationRequired = $state;
    }

    /**
     * @return boolean
     */
    public function hasTemplate() {
        return is_subclass_of($this, 'CM_Mail_Mailable');
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
        $this->getMessage()->setFrom($address, $name);
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
}
