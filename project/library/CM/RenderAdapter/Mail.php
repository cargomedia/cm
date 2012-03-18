<?php

class CM_RenderAdapter_Mail extends CM_RenderAdapter_Abstract {

	public function fetch(array $params = array()) {
		/** @var CM_Mail $mail */
		$mail = $this->_getView();
		if ($mail->getRenderLayout() || $mail->hasTemplate()) {
			$this->getTemplate()->assign($mail->getTplParams());
		}
		if (!($subject = $mail->getSubject())) {
			if (!$mail->hasTemplate()) {
				throw new CM_Exception_Invalid('Trying to render mail with neither subject nor template');
			}
			$subject = $this->getTemplate()->fetch($this->_getTplPath('subject.tpl'));
			$subject = trim($subject);
		}
		if (!($htmlBody = $mail->getHtml()) && $mail->hasTemplate()) {
			$htmlBody = $this->getTemplate()->fetch($this->_getTplPath('body.tpl'));
		}
		if ($mail->getRenderLayout()) {
			$this->getTemplate()->assign('subject', $subject);
			$this->getTemplate()->assign('body', $htmlBody);
			$html = $this->getTemplate()->fetch($this->getRender()->getLayoutPath('layout/mailHtml.tpl'));
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
			$this->getTemplate()->assign('body', $text);
			$text = $this->getTemplate()->fetch($this->getRender()->getLayoutPath('layout/mailText.tpl'));
		}
		return array($subject, $html, $text);
	}
}
