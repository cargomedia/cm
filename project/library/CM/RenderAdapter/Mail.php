<?php

class CM_RenderAdapter_Mail extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Mail $mail */
		$mail = $this->_getObject();
		if ($mail->getRenderLayout() || $mail->hasTemplate()) {
			$this->getLayout()->assign($mail->getTplParams());
		}
		if (!($subject = $mail->getSubject())) {
			if (!$mail->hasTemplate()) {
				throw new CM_Exception_Invalid('Trying to render mail with neither subject nor template');
			}
			if ($mail->getDemoMode()) {
				$subject = file_get_contents($this->_getTplPath($mail->getTemplate(), 'subject'));
			} else {
				$subject = $this->getLayout()->fetch($this->_getTplPath($mail->getTemplate(), 'subject'));
			}
			$subject = trim($subject);
		}
		if (!($htmlBody = $mail->getHtml()) && $mail->hasTemplate()) {
			if ($mail->getDemoMode()) {
				$htmlBody = file_get_contents($this->_getTplPath($mail->getTemplate()));
			} else {
				$htmlBody = $this->getLayout()->fetch($this->_getTplPath($mail->getTemplate()));
			}
		}
		if ($mail->getRenderLayout()) {
			$this->getLayout()->assign('subject', $subject);
			$this->getLayout()->assign('body', $htmlBody);
			$html = $this->getLayout()->fetch($this->_getTplPath('layout', 'html'));
		} else {
			$html = $htmlBody;
		}
		if (!($text = $mail->getText())) {
			if (!$htmlBody) {
				throw new CM_Exception_Invalid('Mail has neither text nor html content');
			}
			$text = preg_replace('!\n!', ' ', $htmlBody);
			$text = preg_replace(array('!<br\s*/?>!', '!<a .*?href="(.*?)".*?>(.*?)</a>!', '!</?p>!'), array("\n", '$2 ($1)', "\n"), $text);
			$text = preg_replace('!(\n)\s+!', "\n", $text);
			$text = trim(strip_tags($text));
		}
		if ($mail->getRenderLayout()) {
			$this->getLayout()->assign('body', $text);
			$text = $this->getLayout()->fetch($this->_getTplPath('layout', 'text'));
		}
		return array($subject, $html, $text);
	}

	/**
	 * @param string $template Name (without .tpl)
	 * @param string $tplName  OPTIONAL
	 * @return string Tpl path
	 */
	protected function _getTplPath($template, $tplName = 'default') {
		return $this->getRender()->getLayoutPath('mail' . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . $tplName . '.tpl', true);
	}
}
