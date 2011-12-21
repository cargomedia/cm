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
	private $_template;
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
	 * @param mixed   $recipient CM_Model_User OR string
	 * @param string  $template
	 * @param boolean $delayed
	 */
	public function __construct($recipient, $template = null, $delayed = false) {
		$this->_delayed = (bool) $delayed;
		$config = CM_Config::section('site')->Section('official');
		if ($template) {
			if (!file_exists($this->_getTplPath($template))) {
				throw new CM_Exception_Invalid('Invalid template specified');
			}
			$this->_template = (string) $template;
			$this->setRenderLayout(true);
		}
		if (is_string($recipient)) {
			$this->_recipientAddress = $recipient;
		} elseif ($recipient instanceof CM_Model_User) {
			$this->_recipient = $recipient;
			$this->_recipientAddress = $this->_recipient->getEmail();
			parent::setTplParam('recipient', $recipient);
		} else {
			throw new CM_Exception_Invalid('No Recipient defined.');
		}
		parent::setTplParam('siteName', $config->site_name);
		parent::setTplParam('siteUrl', SITE_URL);
		$this->_senderAddress = $config->no_reply_email;
		$this->_senderName = $config->site_name;
	}

	/**
	 * @return boolean
	 */
	public function getDemoMode() {
		return $this->_demoMode;
	}

	/**
	 * @param boolean $state
	 */
	public function setDemoMode($state = true) {
		$this->_demoMode = (boolean) $state;
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

	public function getHtmlLayoutTplPath() {
		return $this->_getTplPath('layout', 'html');
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
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getSubjectTplPath() {
		if ($this->hasTemplate()) {
			return $this->_getTplPath($this->_template, 'subject');
		}
		throw new CM_Exception_Invalid('Mail has no template');
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
	 * @return string
	 */
	public function getTextLayoutTplPath() {
		return $this->_getTplPath('layout', 'text');
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function setTplParam($key, $value = null) {
		if (!$this->_template) {
			throw new CM_Exception_Invalid("Can't assign variables when there is no template specified!");
		}
		parent::setTplParam($key, $value);
	}

	/**
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getTplPath() {
		if ($this->hasTemplate()) {
			return $this->_getTplPath($this->_template);
		}
		throw new CM_Exception_Invalid('Mail has no template');
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
		return (boolean) $this->_template;
	}

	/**
	 * @return array|null ($subject, $html, $text)
	 */
	public function send() {
		if (!$this->_recipientAddress) {
			return null;
		}
		if ($this->_verificationRequired && $this->_recipient && !$this->_recipient->getEmailVerified()) {
			return null;
		}
		list($subject, $html, $text) = CM_Render::getInstance()->render($this);
		if ($this->_delayed) {
			$this->_queue($text, $html);
		} else {
			self::_send($this->_subject, $text, $this->_senderAddress, $this->_recipientAddress, $this->_senderName, $html);
		}
		return array($subject, $html, $text);
	}

	/**
	 * @param string $template Name (without .tpl)
	 * @param string $tplName  OPTIONAL
	 * @return string Tpl path
	 */
	private function _getTplPath($template, $tplName = 'default') {
		return CM_Render::getInstance()->getLayoutPath('mail' . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . $tplName . '.tpl', true);
	}

	/**
	 * @return int
	 */
	public static function getQueueSize() {
		return CM_Mysql::count(TBL_EMAIL_QUEUE);
	}

	/**
	 * @param int $limit
	 */
	public static function processQueue($limit) {
		$result = CM_Mysql::execRead("SELECT * FROM TBL_EMAIL_QUEUE ORDER BY `createStamp` LIMIT ?", (int) $limit);
		while ($row = $result->fetchAssoc()) {
			self::_send($row['subject'], $row['text'], $row['senderAddress'], $row['recipientAddress'], $row['senderName'], $row['html']);
			CM_Mysql::delete(TBL_EMAIL_QUEUE, array('id' => $row['id']));
		}
	}

	private function _queue($text, $html) {
		CM_Mysql::insert(TBL_EMAIL_QUEUE, array('subject' => $this->_subject, 'text' => $text, 'html' => $html,
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
		require_once DIR_PHPMAILER . 'class.phpmailer.php';
		if (Config::get()->debug) {
			self::_log($subject, $text, $senderAddress, $recipientAddress);
		} else {
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

				$result = $mail->Send();
			} catch (phpmailerException $e) {
			}
		}
	}
}
