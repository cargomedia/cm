<?php

class CM_Mail {

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
	 * @var array
	 */
	private $_variables;
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
	 * @var Smarty
	 */
	private static $_layout;
	/**
	 * @var CM_TreeNode
	 */
	private static $_section;

	/**
	 * @param mixed $recipient CM_Model_User OR string
	 * @param string $template
	 * @param boolean $delayed
	 */
	public function __construct($recipient, $template = null, $delayed = false) {
		$this->_delayed = (bool) $delayed;
		$config = CM_Config::section('site')->Section('official');
		if (!self::$_section) {
			self::$_section = CM_Language::section('mail_template');
		}
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
			$this->_variables['recipient'] = $recipient;
		} else {
			throw new CM_Exception_Invalid('No Recipient defined.');
		}
		$this->_variables['siteName'] = $config->site_name;
		$this->_variables['siteEmailMain'] = $config->site_email_main;
		$this->_variables['siteEmailBilling'] = $config->site_email_billing;
		$this->_variables['siteEmailSupport'] = $config->site_email_support;
		$this->_variables['siteUrl'] = SITE_URL;
		$this->_variables['siteContactLink'] = SITE_URL . 'about/contact';
		$this->_senderAddress = $config->no_reply_email;
		$this->_senderName = $config->site_name;
		if (!self::$_layout) {
			self::$_layout = CM_Render::getInstance()->getLayout();
		}
	}

	/**
	 * @param string $template Name (without .tpl)
	 * @return string Tpl path
	 */
	private function _getTplPath($template) {
		return CM_Render::getInstance()->getLayoutPath('mail/' . $template . '.tpl', true);
	}

	public static function getTemplates() {
		$section = CM_Language::section('mail_template');
		$keys = array_keys($section->getLeaves());
		return str_replace('_subject', '', $keys);
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function assign($key, $value = null) {
		if (!$this->_template) {
			throw new CM_Exception_Invalid("Can't assign variables when there is no template specified!");
		}
		$this->_variables[$key] = $value;
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
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->_subject = $subject;
	}

	/**
	 * @param string $text
	 */
	public function setText($text) {
		$this->_textBody = $text;
	}

	/**
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->_htmlBody = $html;
	}

	/**
	 * @param boolean $state OPTIONAL
	 */
	public function setVerificationRequired($state = true) {
		$this->_verificationRequired = $state;
	}

	/**
	 * @param boolean $state
	 */
	public function setDemoMode($state = true) {
		$this->_demoMode = $state;
	}

	/**
	 * @param boolean $state OPTIONAL
	 */
	public function setRenderLayout($state = true) {
		$this->_renderLayout = $state;
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
		list($subject, $html, $text) = $this->render();
		if ($this->_delayed) {
			$this->_queue($text, $html);
		} else {
			self::_send($this->_subject, $text, $this->_senderAddress, $this->_recipientAddress, $this->_senderName, $html);
		}
		return array($subject, $html, $text);
	}

	/**
	 * @return array ($subject, $html, $text)
	 */
	public function render() {
		if ($this->_renderLayout || $this->_template) {
			foreach ($this->_variables as $key => $value) {
				self::$_layout->assign($key, $value);
			}
			self::$_layout->assign('subject', $this->_getSubject());
		}
		$html = $this->_getHtml();
		if ($this->_renderLayout) {
			self::$_layout->assign('body', $html);
			$html = self::$_layout->fetch($this->_getTplPath('layout/html'));
		}
		$text = $this->_getText();
		if ($this->_renderLayout) {
			self::$_layout->assign('body', $text);
			$text = self::$_layout->fetch($this->_getTplPath('layout/text'));
		}
		return array($this->_getSubject(), $html, $text);
	}

	private function _getText() {
		if (!$this->_textBody) {
			if ($html = $this->_getHtml()) {
				$text = preg_replace('!\n!', ' ', $html);
				$text = preg_replace(array('!<br\s*/?>!', '!<a .*?href="(.*?)".*?>(.*?)</a>!', '!</?p>!'), array("\n", '$2 ($1)', "\n"), $text);
				$text = preg_replace('!(\n)\s+!', "\n", $text);
				$this->_textBody = trim(strip_tags($text));
			} else {
				throw new CM_Exception_Invalid('No body or template defined.');
			}
		}
		return $this->_textBody;
	}

	private function _getHtml() {
		if (!$this->_htmlBody && $this->_template) {
			$tplPath = $this->_getTplPath($this->_template);
			if ($this->_demoMode) {
				$this->_htmlBody = file_get_contents($tplPath);
			} else {
				$this->_htmlBody = self::$_layout->fetch($tplPath);
			}
		}
		return $this->_htmlBody;
	}

	private function _getSubject() {
		if (!$this->_subject) {
			if (!$this->_template) {
				throw new CM_Exception_Invalid('No subject or template defined');
			}
			if (!(self::$_section->hasLeaf($this->_template . '_subject'))) {
				throw new CM_Exception_Invalid('Subject for template `' . $this->_template . '` does not exist.');
			}
			$this->_subject = self::$_section->text($this->_template . '_subject', $this->_variables);
		}
		return $this->_subject;
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

	private function _queue($text, $html) {
		CM_Mysql::insert(TBL_EMAIL_QUEUE,
				array('subject' => $this->_subject, 'text' => $text, 'html' => $html, 'senderAddress' => $this->_senderAddress,
						'recipientAddress' => $this->_recipientAddress, 'senderName' => $this->_senderName, 'createStamp' => time()));
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
	/**
	 * @return int
	 */
	public static function getQueueSize() {
		return CM_Mysql::count(TBL_EMAIL_QUEUE);
	}
}
