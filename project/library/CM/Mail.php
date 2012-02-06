<?php

class CM_Mail extends CM_Renderable_Abstract {

	/**
	 * @var CM_Model_User
	 */
	private $_recipient;
	/**
	 * @var array
	 */
	private $_to = array();
	/**
	 * @var array
	 */
	private $_replyTo = array();
	/**
	 * @var array
	 */
	private $_cc = array();
	/**
	 * @var array
	 */
	private $_bcc = array();
	/**
	 * @var array
	 */
	private $_sender;
	/**
	 * @var string
	 */
	private $_subject;
	/**
	 * @var string
	 */
	private $_textBody;
	/**
	 * @var string
	 */
	private $_htmlBody;
	/**
	 * @var boolean
	 */
	private $_verificationRequired = true;
	/**
	 * @var boolean
	 */
	private $_renderLayout = false;

	/**
	 * @param CM_Model_User|string|null $recipient
	 * @param array|null				$tplParams
	 */
	public function __construct($recipient = null, array $tplParams = null) {
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
			} elseif ($recipient instanceof CM_Model_User) {
				$this->_recipient = $recipient;
				$this->addTo($this->_recipient->getEmail());
				$this->setTplParam('recipient', $recipient);
			} else {
				throw new CM_Exception_Invalid('Invalid Recipient defined.');
			}
		}

		$config = self::_getConfig();
		$this->setTplParam('siteName', $config->siteName);
		$this->setTplParam('siteUrl', URL_ROOT);
		$this->setSender($config->siteEmailAddress, $config->siteName);
	}

	/**
	 * @param string	  $address
	 * @param string|null $name
	 */
	public function addTo($address, $name = null) {
		$address = (string) $address;
		$name = is_null($name) ? $name : (string) $name;
		$this->_to[] = array('address' => $address, 'name' => $name);
	}

	/**
	 * @return array
	 */
	public function getTo() {
		return $this->_to;
	}

	/**
	 * @param string	  $address
	 * @param string|null $name
	 */
	public function addReplyTo($address, $name = null) {
		$address = (string) $address;
		$name = is_null($name) ? $name : (string) $name;
		$this->_replyTo[] = array('address' => $address, 'name' => $name);
	}

	/**
	 * @return array
	 */
	public function getReplyTo() {
		return $this->_replyTo;
	}

	/**
	 * @param string	  $address
	 * @param string|null $name
	 */
	public function addCc($address, $name = null) {
		$address = (string) $address;
		$name = is_null($name) ? $name : (string) $name;
		$this->_cc[] = array('address' => $address, 'name' => $name);
	}

	/**
	 * @return array
	 */
	public function getCc() {
		return $this->_cc;
	}

	/**
	 * @param string	  $address
	 * @param string|null $name
	 */
	public function addBcc($address, $name = null) {
		$address = (string) $address;
		$name = is_null($name) ? $name : (string) $name;
		$this->_bcc[] = array('address' => $address, 'name' => $name);
	}

	/**
	 * @return array
	 */
	public function getBcc() {
		return $this->_bcc;
	}

	/**
	 * @return string|null
	 */
	public function getHtml() {
		return $this->_htmlBody;
	}

	/**
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->_htmlBody = $html;
	}

	/**
	 * @return array
	 */
	public function getSender() {
		return $this->_sender;
	}

	/**
	 * @param string	  $address
	 * @param string|null $name
	 */
	public function setSender($address, $name = null) {
		$address = (string) $address;
		$name = is_null($name) ? $name : (string) $name;
		$this->_sender = array('address' => $address, 'name' => $name);

	}

	/**
	 * @return string|null
	 */
	public function getSubject() {
		return $this->_subject;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->_subject = $subject;
	}

	/**
	 * @return string|null
	 */
	public function getText() {
		return $this->_textBody;
	}

	/**
	 * @param string $text
	 */
	public function setText($text) {
		$this->_textBody = $text;
	}

	/**
	 * @param boolean $state OPTIONAL
	 */
	public function setVerificationRequired($state = true) {
		$this->_verificationRequired = $state;
	}

	/**
	 * @return boolean
	 */
	public function getRenderLayout() {
		return $this->_renderLayout;
	}

	/**
	 * @param boolean $state OPTIONAL
	 */
	public function setRenderLayout($state = true) {
		$this->_renderLayout = (boolean) $state;
	}

	/**
	 * @return boolean
	 */
	public function hasTemplate() {
		return is_subclass_of($this, 'CM_Mail');
	}

	/**
	 * @param CM_Site_Abstract|null $site
	 * @param boolean|null          $delayed
	 * @return array|null ($subject, $html, $text)
	 */
	public function send(CM_Site_Abstract $site = null, $delayed = null) {
		$delayed = (boolean) $delayed;
		if (!$site) {
			if ($this->_recipient) {
				$site = $this->_recipient->getSite();
			}
		}
		if (empty($this->_to)) {
			throw new CM_Exception_Invalid('No recipient specified.');
		}
		if ($this->_verificationRequired && $this->_recipient && !$this->_recipient->getEmailVerified()) {
			return null;
		}
		$render = new CM_Render($site);
		list($subject, $html, $text) = $render->render($this);
		if ($delayed) {
			$this->_queue($subject, $text, $html);
		} else {
			$this->_send($subject, $text, $html);
		}
		return array($subject, $html, $text);
	}

	/**
	 * @param CM_Site_Abstract|null $site
	 */
	public function sendDelayed(CM_Site_Abstract $site = null) {
		$this->send($site, true);
	}

	/**
	 * @return int
	 */
	public static function getQueueSize() {
		return CM_Mysql::count(TBL_CM_MAIL);
	}

	/**
	 * @param int $limit
	 */
	public static function processQueue($limit) {
		$result = CM_Mysql::execRead("SELECT * FROM TBL_CM_MAIL ORDER BY `createStamp` LIMIT ?", (int) $limit);
		while ($row = $result->fetchAssoc()) {
			$mail = new CM_Mail();
			foreach (unserialize($row['to']) as $to) {
				$mail->addTo($to['address'], $to['name']);
			}
			foreach (unserialize($row['replyTo']) as $replyTo) {
				$mail->addReplyTo($replyTo['address'], $replyTo['name']);
			}
			foreach (unserialize($row['cc']) as $cc) {
				$mail->addCc($cc['address'], $cc['name']);
			}
			foreach (unserialize($row['bcc']) as $bcc) {
				$mail->addBcc($bcc['address'], $bcc['name']);
			}
			$sender = unserialize($row['sender']);
			$mail->setSender($sender['address'], $sender['name']);
			$mail->_send($row['subject'], $row['text'], $row['html']);
			CM_Mysql::delete(TBL_CM_MAIL, array('id' => $row['id']));
		}
	}

	public static function processQueueLegacy() {
		$result = CM_Mysql::execRead("SELECT * FROM TBL_CM_MAIL ORDER BY `createStamp`");
		while ($row = $result->fetchAssoc()) {
			//self::_send($row['subject'], $row['text'], $row['senderAddress'], $row['recipientAddress'], $row['senderName'], $row['html']);
			$mail = new CM_Mail();
			$mail->setSender($row['senderAddress'], $row['senderName']);
			$mail->addTo($row['recipientAddress']);
			$mail->_send($row['subject'], $row['text'], $row['html']);
		}
		CM_Mysql::truncate(TBL_CM_MAIL);
	}

	private function _queue($subject, $text, $html) {
		CM_Mysql::insert(TBL_CM_MAIL, array('subject' => $subject, 'text' => $text, 'html' => $html, 'createStamp' => time(),
			'sender' => serialize($this->getSender()), 'replyTo' => serialize($this->getReplyTo()), 'to' => serialize($this->getTo()),
			'cc' => serialize($this->getCc()), 'bcc' => serialize($this->getBcc())));
	}

	private function _log($subject, $text) {
		$msg = '* ' . $subject . ' *' . PHP_EOL . PHP_EOL;
		$msg .= $text . PHP_EOL;
		$log = new CM_Paging_Log_Mail();
		$log->add($this, $msg);
	}

	private function _send($subject, $text, $html = null) {
		if (CM_Config::get()->debug) {
			$this->_log($subject, $text);
		} else {
			require_once DIR_PHPMAILER . 'class.phpmailer.php';
			try {
				$mail = new PHPMailer(true);

				foreach ($this->_replyTo as $replyTo) {
					$mail->AddReplyTo($replyTo['address'], $replyTo['name']);
				}
				foreach ($this->_to as $to) {
					$mail->AddAddress($to['address'], $to['name']);
				}
				foreach ($this->_cc as $cc) {
					$mail->AddCC($cc['address'], $cc['name']);
				}
				foreach ($this->_bcc as $bcc) {
					$mail->AddBCC($bcc['address'], $bcc['name']);
				}
				$mail->SetFrom($this->_sender['address'], $this->_sender['name']);

				$mail->Subject = $subject;
				$mail->IsHTML($html);
				$mail->Body = $html ? $html : $text;
				$mail->AltBody = $html ? $text : '';

				$mail->Send();
			} catch (phpmailerException $e) {
				throw new CM_Exception_Invalid('Cannot send email, phpmailer reports: ' . $e->getMessage());
			}
		}
	}
}
