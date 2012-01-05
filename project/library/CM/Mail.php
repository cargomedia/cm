<?php

class CM_Mail extends CM_Renderable_Abstract {

	/**
	 * @var CM_Model_User
	 */
	private $_recipient;
	/**
	 * @var string
	 */
	private $_recipientAddress;
	/**
	 * @var string
	 */
	private $_senderAddress;
	/**
	 * @var string
	 */
	private $_senderName;
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
	 * @var boolean
	 */
	private $_delayed;
	/**
	 * @var boolean
	 */
	private $_demoMode = false;

	/**
	 * @param CM_Model_User|string $recipient
	 * @param array $tplParams
	 * @param boolean $delayed
	 */
	public function __construct($recipient, array $tplParams = null, $renderLayout = true, $delayed = false) {
		$this->_delayed = (bool) $delayed;
		if ($this->hasTemplate()) {
			$this->setRenderLayout(true);
		}
		if ($tplParams) {
			foreach ($tplParams as $key => $value) {
				$this->setTplParam($key, $value);
			}
		}
		if (is_string($recipient)) {
			$this->_recipientAddress = $recipient;
		} elseif ($recipient instanceof CM_Model_User) {
			$this->_recipient = $recipient;
			$this->_recipientAddress = $this->_recipient->getEmail();
			$this->setTplParam('recipient', $recipient);
		} else {
			throw new CM_Exception_Invalid('No Recipient defined.');
		}
		$config = self::_getConfig();
		$this->setTplParam('siteName', $config->siteName);
		$this->setTplParam('siteUrl', URL_ROOT);
		$this->_senderAddress = $config->siteEmailAddress;
		$this->_senderName = $config->siteName;
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
	 * @param string $email
	 */
	public function setSenderAddress($email) {
		$this->_senderAddress = $email;
	}

	/**
	 * @param string $name
	 */
	public function setSenderName($name) {
		$this->_senderName = $name;
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
	 * @return array|null ($subject, $html, $text)
	 */
	public function send(CM_Site_Abstract $site = null) {
		if (!$site) {
			if ($this->_recipient) {
				$site = $this->_recipient->getSite();
			}
		}
		if (!$this->_recipientAddress) {
			return null;
		}
		if ($this->_verificationRequired && $this->_recipient && !$this->_recipient->getEmailVerified()) {
			return null;
		}
		list($subject, $html, $text) = CM_Render::getInstance($site)->render($this);
		if ($this->_delayed) {
			$this->_queue($text, $html);
		} else {
			self::_send($subject, $text, $this->_senderAddress, $this->_recipientAddress, $this->_senderName, $html);
		}
		return array($subject, $html, $text);
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
			self::_send($row['subject'], $row['text'], $row['senderAddress'], $row['recipientAddress'], $row['senderName'], $row['html']);
			CM_Mysql::delete(TBL_CM_MAIL, array('id' => $row['id']));
		}
	}

	private function _queue($text, $html) {
		CM_Mysql::insert(TBL_CM_MAIL, array('subject' => $this->_subject, 'text' => $text, 'html' => $html,
			'senderAddress' => $this->_senderAddress, 'recipientAddress' => $this->_recipientAddress, 'senderName' => $this->_senderName,
			'createStamp' => time()));
	}

	private static function _log($subject, $text, $senderAddress, $recipientAddress) {
		$msg = '* ' . $subject . ' *' . PHP_EOL . PHP_EOL;
		$msg .= $text . PHP_EOL;
		$log = new CM_Paging_Log_Mail();
		$log->add($msg, $senderAddress, $recipientAddress);
	}

	private static function _send($subject, $text, $senderAddress, $recipientAddress, $senderName, $html = null) {
		if (CM_Config::get()->debug) {
			self::_log($subject, $text, $senderAddress, $recipientAddress);
		} else {
			require_once DIR_PHPMAILER . 'class.phpmailer.php';
			try {
				$mail = new PHPMailer(true);

				$mail->SetFrom($senderAddress, $senderName);
				$mail->AddReplyTo($senderAddress, $senderName);
				$mail->Sender = $senderAddress;
				$mail->AddAddress($recipientAddress);
				$mail->Subject = $subject;
				$mail->IsHTML($html);
				$mail->Body = $html ? $html : $text;
				$mail->AltBody = $html ? $text : '';

				$mail->Send();
			} catch (phpmailerException $e) {
			}
		}
	}
}
